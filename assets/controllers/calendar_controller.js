import { Controller } from "@hotwired/stimulus";

// ===== Helpers =====
function isPast(date) {
    const t = typeof date === "string" ? new Date(date) : date;
    return t.getTime() <= Date.now();
}
function fmtDateFr(d, timeZone = "Europe/Paris") {
    return new Intl.DateTimeFormat("fr-FR", {
        timeZone,
        weekday: "long",
        day: "2-digit",
        month: "long",
        year: "numeric",
    }).format(d);
}
function fmtTimeFr(d, timeZone = "Europe/Paris") {
    return new Intl.DateTimeFormat("fr-FR", {
        timeZone,
        hour: "2-digit",
        minute: "2-digit",
        hour12: false,
    }).format(d);
}

export default class extends Controller {
    static values = {
        endpoint: String, // ex: "/api/fixed-slots-range"
        typeId: Number, // ex: 12
        startInputSelector: { type: String, default: "#appointment_startAt" },
        initialView: { type: String, default: "timeGridWeek" },
        slotMinTime: { type: String, default: "08:00:00" },
        slotMaxTime: { type: String, default: "20:00:00" },
        timeZone: { type: String, default: "Europe/Paris" },
        firstDay: { type: Number, default: 1 },
    };

    connect() {
        const fc = window.FullCalendar;

        // Recherche de l'input caché
        this.startInput =
            document.querySelector(this.startInputSelectorValue) ||
            document.querySelector("[name$='[startAt]']") ||
            document.querySelector("[id$='_startAt']");
        if (!this.startInput) {
            console.warn("[calendar] Champ startAt introuvable.");
        }

        this.selectedEvent = null;
        this.loadingEl = this.ensureLoadingEl();

        // ===== Initialisation FullCalendar =====
        this.calendar = new fc.Calendar(this.element, {
            themeSystem: "bootstrap5",
            locale: "fr",
            initialView: this.initialViewValue,
            firstDay: this.firstDayValue,
            timeZone: this.timeZoneValue,
            slotMinTime: this.slotMinTimeValue,
            slotMaxTime: this.slotMaxTimeValue,
            nowIndicator: true,
            selectable: false,
            allDaySlot: false, // ⬅️ Masque la ligne "Toute la journée"
            stickyHeaderDates: true,
            expandRows: true,
            height: "auto",
            slotDuration: "00:30:00",
            slotLabelFormat: {
                hour: "2-digit",
                minute: "2-digit",
                hour12: false,
            },
            eventTimeFormat: {
                hour: "2-digit",
                minute: "2-digit",
                hour12: false,
            },
            headerToolbar: {
                left: "prev,next today",
                center: "title",
                right: "timeGridWeek", // vue unique (mobile-friendly)
            },
            validRange: { start: new Date() },
            events: (info, success, failure) =>
                this.loadRange(info)
                    .then((events) => {
                        this.toggleEmptyNotice(events.length === 0);
                        this.renderList(events); // ⬅️ met à jour la liste
                        success(events);
                    })
                    .catch((e) => {
                        console.error("[calendar] events load error", e);
                        this.toggleEmptyNotice(true);
                        this.renderList([]); // ⬅️ vide la liste
                        failure(e);
                    }),
            eventClick: (arg) => this.onEventClick(arg),
            eventMouseEnter: (info) => {
                info.el.style.cursor = "pointer";
            },
        });

        this.calendar.render();

        // UI en dehors du conteneur FC
        this.ensureEmptyNoticeEl();
        this.ensureListEl();
    }

    disconnect() {
        if (this.calendar) this.calendar.destroy();
        if (this.listEl)
            this.listEl.removeEventListener("click", this.onListClickBound);
    }

    // ===== Chargement par range =====
    async loadRange(info) {
        this.setLoading(true);

        const url = new URL(this.endpointValue, window.location.origin);
        url.searchParams.set("type", String(this.typeIdValue));
        const ymd = (d) => d.toISOString().slice(0, 10);
        url.searchParams.set("start", ymd(info.start));
        url.searchParams.set("end", ymd(info.end));

        try {
            const resp = await fetch(url.toString(), {
                headers: { "X-Requested-With": "XMLHttpRequest" },
            });
            if (!resp.ok) return [];

            const data = await resp.json(); // [{start,end}]
            return data.map((e) => {
                const past = isPast(e.end || e.start);
                return {
                    ...e,
                    display: "block",
                    backgroundColor: past ? "#e9ecef" : "#d1e7dd",
                    borderColor: past ? "#ced4da" : "#198754",
                    textColor: past ? "#6c757d" : "#0f5132",
                    extendedProps: {
                        disabled: past,
                        _defaultBg: past ? "#e9ecef" : "#d1e7dd",
                        _defaultBorder: past ? "#ced4da" : "#198754",
                        _defaultText: past ? "#6c757d" : "#0f5132",
                    },
                };
            });
        } finally {
            this.setLoading(false);
        }
    }

    // ===== Clic sur un créneau (depuis le calendrier) =====
    onEventClick(arg) {
        if (
            arg.event.extendedProps?.disabled ||
            isPast(arg.event.end || arg.event.start)
        )
            return;
        if (!this.startInput) return;

        // Conversion simple vers <input type="datetime-local">
        const iso = arg.event.startStr; // ex: 2025-10-08T14:00:00+02:00
        const local = iso.replace(/([+-]\d{2}:\d{2}|Z)$/, "").slice(0, 16);

        this.startInput.value = local;
        this.startInput.dispatchEvent(new Event("input", { bubbles: true }));
        this.startInput.dispatchEvent(new Event("change", { bubbles: true }));
        console.debug("[calendar] startAt rempli :", local);

        // Feedback visuel dans le calendrier
        if (this.selectedEvent) {
            const ep = this.selectedEvent.extendedProps || {};
            this.selectedEvent.setProp(
                "backgroundColor",
                ep._defaultBg || "#d1e7dd"
            );
            this.selectedEvent.setProp(
                "borderColor",
                ep._defaultBorder || "#198754"
            );
            this.selectedEvent.setProp(
                "textColor",
                ep._defaultText || "#0f5132"
            );
        }
        arg.event.setProp("backgroundColor", "#cfe2ff");
        arg.event.setProp("borderColor", "#0d6efd");
        arg.event.setProp("textColor", "#084298");
        this.selectedEvent = arg.event;

        // Feedback dans la liste (actif)
        this.highlightListItem(iso);
    }

    // ===== UI: Loader =====
    ensureLoadingEl() {
        let el = this.element.querySelector(".fc-loading-indicator");
        if (!el) {
            el = document.createElement("div");
            el.className = "fc-loading-indicator";
            el.style.cssText =
                "position:absolute;top:8px;right:8px;z-index:5;padding:.25rem .5rem;border-radius:.25rem;background:#f8f9fa;border:1px solid #dee2e6;font-size:.85rem;display:none;";
            el.textContent = "Chargement des créneaux…";
            if (!this.element.style.position)
                this.element.style.position = "relative";
            this.element.appendChild(el);
        }
        return el;
    }
    setLoading(isLoading) {
        if (this.loadingEl)
            this.loadingEl.style.display = isLoading ? "block" : "none";
    }

    // ===== UI: Alerte "aucun créneau" =====
    ensureEmptyNoticeEl() {
        let box = this.element.nextElementSibling;
        if (box && box.classList.contains("fc-empty-notice")) {
            this.emptyNoticeEl = box;
            return box;
        }
        box = document.createElement("div");
        box.className = "fc-empty-notice alert alert-warning d-none";
        box.style.cssText = "margin:.5rem 0;";
        box.innerHTML = `
      <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-2">
        <div><strong>Aucun créneau disponible sur cette période.</strong></div>
        <div class="text-muted">Essayez une autre semaine.</div>
        <div class="ms-sm-auto">
          <button type="button" class="btn btn-sm btn-primary fc-next-week">Voir la semaine suivante</button>
        </div>
      </div>
    `;
        if (this.element.parentNode) {
            this.element.parentNode.insertBefore(box, this.element.nextSibling);
        } else {
            document.body.appendChild(box);
        }
        const btn = box.querySelector(".fc-next-week");
        btn?.addEventListener("click", () => this.calendar.next());
        this.emptyNoticeEl = box;
        return box;
    }
    toggleEmptyNotice(show) {
        if (this.emptyNoticeEl)
            this.emptyNoticeEl.classList.toggle("d-none", !show);
    }

    // ===== UI: Liste des créneaux restants =====
    ensureListEl() {
        // Crée / récupère un conteneur sous la bannière
        let after = this.emptyNoticeEl || this.element;
        let el = after.nextElementSibling;
        if (!(el && el.classList && el.classList.contains("fc-slot-list"))) {
            el = document.createElement("div");
            el.className = "fc-slot-list mt-2";
            el.innerHTML = `
        <h6 class="mb-2">Créneaux restants</h6>
        <div class="fc-slot-list-body"></div>
      `;
            if (after.parentNode)
                after.parentNode.insertBefore(el, after.nextSibling);
            else document.body.appendChild(el);
        }
        this.listEl = el;
        this.listBodyEl = el.querySelector(".fc-slot-list-body");

        // Délégation de clic sur les boutons de créneau
        this.onListClickBound = this.onListClick.bind(this);
        this.listEl.addEventListener("click", this.onListClickBound);

        return el;
    }

    renderList(events) {
        if (!this.listBodyEl) return;

        // Garde uniquement les slots "cliquables" (non passés)
        const avail = events
            .filter((e) => !isPast(e.end || e.start))
            .sort((a, b) => new Date(a.start) - new Date(b.start));

        if (avail.length === 0) {
            this.listBodyEl.innerHTML = `
        <div class="text-muted small">Aucun créneau sur cette période.</div>
      `;
            return;
        }

        // Group by date (Europe/Paris)
        const byDay = new Map();
        for (const e of avail) {
            const d = new Date(e.start);
            const key = d.toISOString().slice(0, 10); // YYYY-MM-DD
            if (!byDay.has(key)) byDay.set(key, []);
            byDay.get(key).push(e);
        }

        // Build HTML
        const chunks = [];
        for (const [day, evts] of byDay.entries()) {
            const d = new Date(evts[0].start);
            const dayLabel = fmtDateFr(d, this.timeZoneValue);
            const items = evts
                .map((e) => {
                    const s = new Date(e.start);
                    const en = new Date(e.end);
                    const label = `${fmtTimeFr(
                        s,
                        this.timeZoneValue
                    )} – ${fmtTimeFr(en, this.timeZoneValue)}`;
                    // data-iso garde la valeur startStr avec timezone (que renvoie FullCalendar)
                    // si elle n'est pas disponible ici, on la reconstituera depuis la vue.
                    return `<button type="button"
                    class="btn btn-outline-success btn-sm me-2 mb-2 fc-slot-btn"
                    data-iso="${new Date(e.start).toISOString()}"
                  >${label}</button>`;
                })
                .join("");

            chunks.push(`
        <div class="card mb-2">
          <div class="card-body py-2">
            <div class="fw-semibold mb-2">${dayLabel}</div>
            <div class="d-flex flex-wrap">${items}</div>
          </div>
        </div>
      `);
        }

        this.listBodyEl.innerHTML = chunks.join("");
        this.syncListHighlight(); // surbrillance si un slot est déjà sélectionné
    }

    onListClick(e) {
        const btn = e.target.closest(".fc-slot-btn");
        if (!btn) return;

        // On veut sélectionner l'event correspondant dans FullCalendar
        // On recherche par "minute près" en comparant startStr
        // 1) ISO du bouton (UTC). On doit le convertir au même format que startStr (avec TZ de la vue)
        const isoUtc = btn.getAttribute("data-iso"); // ex: 2025-10-08T12:00:00.000Z

        // 2) Find matching event in calendar by "instant"
        const targetTime = new Date(isoUtc).getTime();
        const candidates = this.calendar
            .getEvents()
            .filter((ev) => ev.start && ev.end);
        let match = null;
        for (const ev of candidates) {
            if (Math.abs(ev.start.getTime() - targetTime) < 60000) {
                // tolérance 1 min
                match = ev;
                break;
            }
        }

        if (match) {
            // Simule un click sur l'event pour mutualiser la logique
            this.onEventClick({ event: match });
            // Met surbrillance dans la liste
            this.highlightListItem(match.start.toISOString());
        }
    }

    // Surbrillance dans la liste pour le start sélectionné
    highlightListItem(startIsoUtc) {
        if (!this.listBodyEl) return;
        const targetMs = new Date(startIsoUtc).getTime();
        this.listBodyEl.querySelectorAll(".fc-slot-btn").forEach((btn) => {
            const ms = new Date(btn.dataset.iso).getTime();
            btn.classList.toggle(
                "btn-primary",
                Math.abs(ms - targetMs) < 60000
            );
            btn.classList.toggle(
                "btn-outline-success",
                Math.abs(ms - targetMs) >= 60000
            );
        });
    }

    // Si on revient sur une vue déjà sélectionnée, garde la cohérence visuelle
    syncListHighlight() {
        if (!this.selectedEvent) return;
        this.highlightListItem(this.selectedEvent.start.toISOString());
    }
}
