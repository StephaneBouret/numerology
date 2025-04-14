export default class Quiz {
    constructor() {
        this.submitButton = document.getElementById('submit-quiz-button');
        this.nextButton = document.getElementById('next-question-button');
        this.viewResultsButton = document.getElementById('view-results-button');
        this.sectionElement = document.getElementById('quiz-section-id');
        this.navigationPartial = document.getElementById('navigation-partial');

        if (!this.sectionElement) {
            console.warn('Aucun élément #quiz-section-id trouvé. Le script de quiz ne sera pas exécuté.');
            return;
        }

        this.sectionId = this.sectionElement.dataset.sectionId;
        this.questions = document.querySelectorAll('.question');

        // ➕ Récupération de la progression
        this.answers = this.loadAnswersFromLocalStorage();
        this.currentQuestionIndex = this.findFirstUnansweredIndex() ?? 0;

        this.init();
    }

    init() {
        if (!this.submitButton || !this.nextButton || !this.viewResultsButton || !this.sectionElement) {
            return;
        }

        console.log('Index initial chargé :', this.currentQuestionIndex);
        this.attachEventListeners();
        this.restoreSelectedAnswers();
        this.showQuestion(this.currentQuestionIndex);
    }

    attachEventListeners() {
        document.querySelectorAll('input[type="radio"]').forEach(input => {
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
    }

    handleAnswerSelection(event) {
        const selectedAnswer = event.target;
        const questionId = selectedAnswer.closest('.question').dataset.questionId;
        const answerId = selectedAnswer.value;

        document.querySelectorAll(`input[name="${selectedAnswer.name}"]`).forEach(input => {
            input.parentElement.classList.remove('selected');
        });
        selectedAnswer.parentElement.classList.add('selected');

        this.answers[questionId] = answerId;
        // this.saveAnswersToLocalStorage();

        this.submitButton.classList.remove('disabled');
        this.submitButton.disabled = false;
    }

    handleSubmit() {
        const currentQuestion = this.questions[this.currentQuestionIndex];
        const selectedAnswer = currentQuestion.querySelector('input[type="radio"]:checked');

        if (!selectedAnswer) {
            // Afficher un message d'erreur dans l'interface utilisateur
            const errorMessage = currentQuestion.querySelector('.error-message');
            if (errorMessage) {
                errorMessage.textContent = 'Veuillez sélectionner une réponse.';
                errorMessage.classList.remove('d-none');
            }
            return;
        }

        this.saveAnswersToLocalStorage();

        fetch("/quiz/submit", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_csrf_token"]').value
                },
                body: JSON.stringify({
                    sectionId: this.sectionId,
                    questionId: currentQuestion.dataset.questionId,
                    answerId: selectedAnswer.value,
                    currentQuestionIndex: this.currentQuestionIndex,
                    attemptId: parseInt(this.sectionElement.dataset.attemptId || 0,
                        10)
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Réponse du serveur:', data);

                // Mettre à jour l'attribut data-attempt-id avec le nouvel ID de tentative
                if (data.attemptId) {
                    this.sectionElement.dataset.attemptId = data.attemptId;
                }
                const feedbackClass = data.correct ? 'correct' : 'incorrect';
                selectedAnswer.parentElement.classList.add(feedbackClass);

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
            })
            .catch(error => {
                console.error('Erreur lors de la soumission de la réponse:', error);
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
            const answerId = this.answers[questionId] || null;

            return {
                questionId: parseInt(questionId, 10),
                answerId: answerId ? parseInt(answerId, 10) : null
            };
        });

        fetch("/quiz/finalize", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_csrf_token"]').value
                },
                body: JSON.stringify({
                    sectionId: this.sectionId,
                    answers: answers
                })
            })
            .then(response => response.json())
            .then(data => {
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
            });
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
        for (const [questionId, answerId] of Object.entries(this.answers)) {
            const question = [...this.questions].find(q => q.dataset.questionId === questionId);
            if (question) {
                const input = question.querySelector(`input[type="radio"][value="${answerId}"]`);
                if (input) {
                    input.checked = true;
                    input.parentElement.classList.add('selected');
                }
            }
        }
    }
}