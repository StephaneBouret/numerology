export default class Quiz {
    constructor() {
        this.submitButton = document.getElementById('submit-quiz-button');
        this.nextButton = document.getElementById('next-question-button');
        this.viewResultsButton = document.getElementById('view-results-button');
        this.sectionElement = document.getElementById('quiz-section-id');
        this.navigationPartial = document.getElementById('navigation-partial');
        this.spinner = document.getElementById('quiz-loading-spinner');
        this.progressBar = document.getElementById('quiz-progress-bar');

        if (!this.sectionElement) {
            console.warn('Aucun élément #quiz-section-id trouvé. Le script de quiz ne sera pas exécuté.');
            return;
        }

        this.sectionId = this.sectionElement.dataset.sectionId;
        this.questions = document.querySelectorAll('.question');

        this.startedAtKey = `quiz_${this.sectionId}_startedAt`;       

        // ➕ Récupération de la progression
        this.answers = this.loadAnswersFromLocalStorage();
        this.currentQuestionIndex = this.findFirstUnansweredIndex() ?? 0;

        this.init();
    }

    init() {
        if (!this.submitButton || !this.nextButton || !this.viewResultsButton || !this.sectionElement) {
            return;
        }

        this.attachEventListeners();
        this.restoreSelectedAnswers();
        this.showQuestion(this.currentQuestionIndex);
    }

    attachEventListeners() {
        document.querySelectorAll('input[type="radio"], input[type="checkbox"]').forEach(input => {
            input.addEventListener('change', this.handleAnswerSelection.bind(this));
        });

        this.submitButton.addEventListener('click', this.handleSubmit.bind(this));
        this.nextButton.addEventListener('click', this.handleNextQuestion.bind(this));
        this.viewResultsButton.addEventListener('click', this.handleViewResults.bind(this));
    }

    showQuestion(index) {
        this.questions.forEach((question, i) => {
            question.classList.toggle('d-none', i !== index);
        });

        const currentQuestion = this.questions[index];
        const selectedAnswer = currentQuestion.querySelector('input[type="radio"]:checked');

        this.submitButton.classList.add('disabled');
        this.submitButton.disabled = true;
        this.nextButton.classList.add('d-none');
        this.viewResultsButton.classList.add('d-none');

        if (selectedAnswer) {
            // Une réponse est sélectionnée : on affiche "Suivant" ou "Voir les résultats"
            if (index === this.questions.length - 1) {
                this.viewResultsButton.classList.remove('d-none');
                this.submitButton.classList.add('d-none');
            } else {
                this.nextButton.classList.remove('d-none');
                this.submitButton.classList.add('d-none');
            }
        } else {
            // Aucune réponse sélectionnée : afficher "Valider"
            if (index === this.questions.length - 1) {
                this.submitButton.classList.remove('d-none');
                this.viewResultsButton.classList.add('d-none');
            } else {
                this.submitButton.classList.remove('d-none');
                this.nextButton.classList.add('d-none');
            }
        }

        this.updateProgressBar();
    }

    handleAnswerSelection(event) {
        const selectedAnswer = event.target;
        const questionElement = selectedAnswer.closest('.question');
        const questionId = questionElement.dataset.questionId;
        const isMultiple = questionElement.dataset.multiple === '1';

        if (isMultiple) {
            // Init tableau si vide
            if (!Array.isArray(this.answers[questionId])) {
                this.answers[questionId] = [];
            }

            const answerId = selectedAnswer.value.toString(); // <- transforme en string pour comparer

            const index = this.answers[questionId].indexOf(answerId);

            if (selectedAnswer.checked && index === -1) {
                this.answers[questionId].push(answerId);
                selectedAnswer.parentElement.classList.add('selected');
            } else if (!selectedAnswer.checked && index !== -1) {
                this.answers[questionId].splice(index, 1);
                selectedAnswer.parentElement.classList.remove('selected');
            }

        } else {
            const answerId = selectedAnswer.value.toString();
            this.answers[questionId] = answerId;

            // Visuel sélection pour radio
            document.querySelectorAll(`input[name="${selectedAnswer.name}"]`).forEach(input => {
                input.parentElement.classList.remove('selected');
            });
            selectedAnswer.parentElement.classList.add('selected');
        }

        if (!localStorage.getItem(this.startedAtKey)) {
            const startedAt = new Date().toISOString();
            localStorage.setItem(this.startedAtKey, startedAt);
        }

        this.submitButton.classList.remove('disabled');
        this.submitButton.disabled = false;
    }

    handleSubmit() {
        const currentQuestion = this.questions[this.currentQuestionIndex];
        const questionId = currentQuestion.dataset.questionId;
        const isMultiple = currentQuestion.dataset.multiple === '1';

        let selectedAnswerInputs = currentQuestion.querySelectorAll('input:checked');
        let selectedValues = Array.from(selectedAnswerInputs).map(input => input.value);

        if (!selectedValues.length) {
            const errorMessage = currentQuestion.querySelector('.error-message');
            if (errorMessage) {
                errorMessage.textContent = 'Veuillez sélectionner une réponse.';
                errorMessage.classList.remove('d-none');
            }
            return;
        }

        const payload = {
            sectionId: this.sectionId,
            questionId: questionId,
            answerId: isMultiple ? selectedValues.map(Number) : parseInt(selectedValues[0], 10),
            currentQuestionIndex: this.currentQuestionIndex,
            attemptId: parseInt(this.sectionElement.dataset.attemptId || 0, 10)
        };

        this.saveAnswersToLocalStorage();

        currentQuestion.classList.add('loading');

        currentQuestion.querySelectorAll('input[type="radio"], input[type="checkbox"]').forEach(input => {
            input.disabled = true;
        })

        fetch("/quiz/submit", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_csrf_token"]').value
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                console.log('Réponse du serveur:', data);

                if (data.attemptId) {
                    this.sectionElement.dataset.attemptId = data.attemptId;
                }

                // Nettoyage visuel
                currentQuestion.querySelectorAll('.answer').forEach(li => {
                    li.classList.remove('correct', 'incorrect');
                });

                if (Array.isArray(data.correctAnswers)) {
                    const selectedIds = selectedValues.map(v => parseInt(v, 10));
                    const correctSet = new Set(data.correctAnswers);

                    const isFullyCorrect =
                        selectedIds.length === data.correctAnswers.length &&
                        selectedIds.every(id => correctSet.has(id));

                    const feedback = currentQuestion.querySelector('.feedback');
                    if (feedback) {
                        feedback.textContent = isFullyCorrect ?
                            'Bonne réponse ! ✅' :
                            'Réponse incorrecte ou incomplète ❌';
                        feedback.classList.remove('d-none');
                        feedback.classList.toggle('text-success', isFullyCorrect);
                        feedback.classList.toggle('text-danger', !isFullyCorrect);
                    }

                    selectedAnswerInputs.forEach(input => {
                        const isCorrect = data.correctAnswers.includes(parseInt(input.value, 10));
                        input.parentElement.classList.add(
                            isFullyCorrect ? 'correct' : (isCorrect ? 'correct' : 'incorrect')
                        );
                    });
                } else {
                    selectedAnswerInputs.forEach(input => {
                        input.parentElement.classList.add(data.correct ? 'correct' : 'incorrect');
                    });
                }

                const explanation = currentQuestion.querySelector('.explanation');
                if (explanation) {
                    explanation.classList.remove('d-none');
                }

                if (this.currentQuestionIndex < this.questions.length - 1) {
                    this.nextButton.classList.remove('d-none');
                    this.submitButton.classList.add('d-none');
                } else {
                    this.submitButton.classList.add('d-none');
                    this.viewResultsButton.classList.remove('d-none');
                }

                this.updateProgressBar();
            })
            .catch(error => {
                console.error('Erreur lors de la soumission de la réponse:', error);
            })
            .finally(() => {
                currentQuestion.classList.remove('loading');
            });
    }

    handleNextQuestion() {
        this.currentQuestionIndex++;
        this.saveCurrentQuestionIndexToLocalStorage();

        if (this.currentQuestionIndex === 1 && this.navigationPartial) {
            this.navigationPartial.classList.add('d-none');
        }

        if (this.currentQuestionIndex < this.questions.length) {
            this.showQuestion(this.currentQuestionIndex);
            this.submitButton.classList.remove('d-none');
            this.nextButton.classList.add('d-none');
            this.navigationPartial.classList.add('d-none');
        } else {
            this.handleViewResults();
        }
    }

    handleViewResults() {
        const answers = Array.from(this.questions).map(question => {
            const questionId = question.dataset.questionId;
            const isMultiple = question.dataset.multiple === '1';
            const raw = this.answers[questionId];

            return {
                questionId: parseInt(questionId, 10),
                answerId: isMultiple ? raw.map(Number) : parseInt(raw, 10) || null
            };
        });

        const startedAt = localStorage.getItem(this.startedAtKey);

        // 👉 Afficher le spinner
        if (this.spinner) this.spinner.classList.remove('d-none');
        const form = document.querySelector('.quiz-form');
        if (form) form.classList.add('loading');

        fetch("/quiz/finalize", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_csrf_token"]').value
                },
                body: JSON.stringify({
                    sectionId: this.sectionId,
                    answers: answers,
                    startedAt: startedAt,
                })
            })
            .then(response => response.json())
            .then(data => {
                // ✅ Mettre à jour la barre de progression à 100%
                if (this.progressBar) {
                    this.progressBar.style.width = '100%';
                    this.progressBar.setAttribute('aria-valuenow', 100);
                }
                // 🧹 Nettoyage du localStorage
                this.clearLocalStorage();

                if (data.redirectUrl) {
                    window.location.href = data.redirectUrl;
                } else {
                    console.error("Erreur lors de l'enregistrement des résultats:", data.error);
                }
            })
            .catch(error => {
                console.error('Erreur lors de la finalisation du quiz:', error);
            })
            .finally(() => {
                // 👉 Cacher le spinner
                if (this.spinner) this.spinner.classList.add('d-none');
                if (form) form.classList.remove('loading');
            });
    }

    updateProgressBar() {
        if (!this.progressBar) return;

        const totalQuestions = this.questions.length;
        const answeredCount = Object.keys(this.answers).length;
        const progressPercent = Math.round((answeredCount / totalQuestions) * 100);

        this.progressBar.style.width = `${progressPercent}%`;
        this.progressBar.setAttribute('aria-valuenow', progressPercent);
    }

    // 🔐 LocalStorage logic
    getAnswersKey() {
        return `quiz_${this.sectionId}_answers`;
    }

    getIndexKey() {
        return `quiz_${this.sectionId}_currentIndex`;
    }

    saveAnswersToLocalStorage() {
        localStorage.setItem(this.getAnswersKey(), JSON.stringify(this.answers));
    }

    loadAnswersFromLocalStorage() {
        const stored = localStorage.getItem(this.getAnswersKey());
        return stored ? JSON.parse(stored) : {};
    }

    saveCurrentQuestionIndexToLocalStorage() {
        localStorage.setItem(this.getIndexKey(), this.currentQuestionIndex);
    }

    clearLocalStorage() {
        localStorage.removeItem(this.getAnswersKey());
        localStorage.removeItem(this.getIndexKey());
        localStorage.removeItem(this.startedAtKey);
    }

    findFirstUnansweredIndex() {
        for (let i = 0; i < this.questions.length; i++) {
            const questionId = this.questions[i].dataset.questionId;
            if (!this.answers[questionId]) {
                return i;
            }
        }
        return this.questions.length - 1;
    }

    restoreSelectedAnswers() {
        for (const [questionId, answerValue] of Object.entries(this.answers)) {
            const question = [...this.questions].find(q => q.dataset.questionId === questionId);
            if (!question) continue;

            const isMultiple = question.dataset.multiple === '1';

            if (isMultiple && Array.isArray(answerValue)) {
                answerValue.forEach(answerId => {
                    const input = question.querySelector(`input[type="checkbox"][value="${answerId}"]`);
                    if (input) {
                        input.checked = true;
                        input.parentElement.classList.add('selected');
                    }
                });
            } else {
                const input = question.querySelector(`input[type="radio"][value="${answerValue}"]`);
                if (input) {
                    input.checked = true;
                    input.parentElement.classList.add('selected');
                }
            }
        }
    }
}