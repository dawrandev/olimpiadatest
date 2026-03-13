/**
 * Student Test JavaScript
 * Handles test functionality with proper MathJax rendering
 */

// Global configuration
const TestManager = {
    // Configuration
    config: {
        mathJaxConfig: {
            tex: {
                inlineMath: [["\\(", "\\)"]],
                displayMath: [["\\[", "\\]"]],
            },
        },
    },

    // Translation strings (will be populated from window.translations)
    translations: {},

    // Test data
    testData: {},

    // Questions data
    questions: [],

    // State variables
    currentQuestionId: 0,
    currentQuestionIndex: 0,
    selectedAnswers: {},
    completedQuestions: 0,
    answeredQuestions: new Set(),
    totalTimeInSeconds: 0,
    remainingTime: 0,
    timerInterval: null,
    isTimerActive: true,
    testFinished: false,

    // Initialize the test manager
    init() {
        this.translations = window.translations || {};
        this.testData = window.testData || {};
        this.questions = window.questions || [];

        this.setupMathJax();
        this.initializeTest();
        this.bindEvents();
        this.startTimer();
    },

    // Setup MathJax configuration
    setupMathJax() {
        if (typeof MathJax !== "undefined") {
            MathJax = this.config.mathJaxConfig;
        }
    },

    // Initialize test data
    initializeTest() {
        if (this.questions.length > 0) {
            this.currentQuestionId = this.questions[0].id;
            this.currentQuestionIndex = 0;
        }

        this.totalTimeInSeconds = Math.floor(this.testData.timeLimit || 0);
        this.remainingTime = this.totalTimeInSeconds;
    },

    // Bind all event listeners
    bindEvents() {
        this.bindVariantCards();
        this.bindSubmitButtons();
        this.bindNavigationButtons();
        this.bindFinishButton();
    },

    // Bind variant card click events
    bindVariantCards() {
        document.querySelectorAll(".variant-card").forEach((card) => {
            card.addEventListener("click", (e) => {
                if (!this.isTimerActive || this.testFinished) return;

                e.preventDefault();
                e.stopPropagation();

                const answerId = parseInt(card.dataset.answerId, 10);
                const questionId = parseInt(card.dataset.questionId, 10);
                const questionContainer = document.getElementById(
                    `question-${questionId}`
                );

                if (!questionContainer) return;

                // Remove selection from all variants
                const allVariants =
                    questionContainer.querySelectorAll(".variant-card");
                allVariants.forEach((variant) =>
                    variant.classList.remove("selected")
                );

                // Add selection to clicked variant
                card.classList.add("selected");

                // Update radio input
                const radioInput = questionContainer.querySelector(
                    `input[value="${answerId}"]`
                );
                if (radioInput) {
                    questionContainer
                        .querySelectorAll('input[type="radio"]')
                        .forEach((radio) => (radio.checked = false));
                    radioInput.checked = true;
                }

                // Store selected answer
                this.selectedAnswers[questionId] = answerId;

                // Enable submit button
                const submitBtn = document.getElementById(
                    `submitBtn${questionId}`
                );
                if (submitBtn) submitBtn.disabled = false;

                // Add visual feedback
                card.style.animation = "pulse 0.6s ease-in-out";
            });
        });
    },

    // Bind submit button events
    bindSubmitButtons() {
        document.querySelectorAll(".submit-btn").forEach((btn) => {
            btn.addEventListener("click", (e) => {
                if (!this.isTimerActive || this.testFinished) return;

                e.preventDefault();

                const questionId = parseInt(btn.dataset.questionId, 10);
                const selectedAnswerId = this.selectedAnswers[questionId];

                if (!selectedAnswerId) {
                    this.showAlert(
                        this.translations.warningTitle,
                        this.translations.selectAnswer,
                        "warning"
                    );
                    return;
                }

                if (this.testData.routes && this.testData.routes.submitAnswer) {
                    this.submitAnswerToServer(
                        questionId,
                        selectedAnswerId,
                        btn
                    );
                }
            });
        });
    },

    // Bind navigation button events
    bindNavigationButtons() {
        document.querySelectorAll(".nav-btn").forEach((btn) => {
            btn.addEventListener("click", () => {
                if (this.testFinished) return;

                const questionId = parseInt(btn.dataset.questionId, 10);
                const questionIndex = this.questions.findIndex(
                    (q) => q.id === questionId
                );

                if (questionIndex !== -1) {
                    this.currentQuestionIndex = questionIndex;
                    this.showQuestion(questionId, questionIndex + 1);
                }
            });
        });
    },

    // Bind finish button event
    bindFinishButton() {
        const finishBtn = document.querySelector("#finishTestBtn");
        if (finishBtn) {
            finishBtn.addEventListener("click", (e) => {
                e.preventDefault();
                e.stopPropagation();

                this.showConfirmDialog(
                    this.translations.finishTitle,
                    this.translations.finishText,
                    "question",
                    () => this.finishTest(false)
                );
            });
        }
    },

    // Start the timer
    startTimer() {
        const timerDisplay = document.getElementById("timerDisplay");
        const progressCircle = document.getElementById("progressCircle");

        if (!timerDisplay) return;

        let circumference = 0;
        if (progressCircle) {
            circumference = 2 * Math.PI * 35;
            progressCircle.style.strokeDasharray = `${circumference} ${circumference}`;
            progressCircle.style.strokeDashoffset = 0;
            progressCircle.style.transition = "stroke-dashoffset 1s linear";
        }

        timerDisplay.textContent = this.formatTime(this.remainingTime);

        this.timerInterval = setInterval(() => {
            if (!this.isTimerActive || this.testFinished) return;

            this.remainingTime--;

            if (this.remainingTime < 0) {
                this.remainingTime = 0;
            }

            timerDisplay.textContent = this.formatTime(this.remainingTime);

            if (progressCircle && circumference > 0) {
                const progress =
                    (this.totalTimeInSeconds - this.remainingTime) /
                    this.totalTimeInSeconds;
                const offset = circumference * progress;
                progressCircle.style.strokeDashoffset = offset;

                if (this.remainingTime <= 300) {
                    progressCircle.style.stroke = "#ef4444";
                    timerDisplay.style.color = "#ef4444";
                } else if (this.remainingTime <= 600) {
                    progressCircle.style.stroke = "#f59e0b";
                    timerDisplay.style.color = "#f59e0b";
                }
            }

            if (this.remainingTime <= 0) {
                clearInterval(this.timerInterval);
                this.timeUp();
            }
        }, 1000);
    },

    // Format time display
    formatTime(time) {
        const minutes = Math.floor(time / 60);
        let seconds = time % 60;
        if (seconds < 10) seconds = "0" + seconds;
        return `${minutes}:${seconds}`;
    },

    // Handle time up
    timeUp() {
        if (this.testFinished) return;

        this.isTimerActive = false;
        this.testFinished = true;

        document.querySelectorAll(".variant-card").forEach((card) => {
            card.style.pointerEvents = "none";
            card.classList.add("disabled");
        });

        document
            .querySelectorAll(".submit-btn")
            .forEach((btn) => (btn.disabled = true));

        this.showAlert(
            this.translations.timeUpTitle,
            this.translations.timeUpText,
            "warning",
            () => this.finishTest(true)
        );
    },

    // Submit answer to server
    submitAnswerToServer(questionId, answerId, submitBtn) {
        const questionContainer = document.getElementById(
            `question-${questionId}`
        );
        const selectedCard = questionContainer?.querySelector(
            `[data-answer-id="${answerId}"]`
        );
        const studentAnswerId = selectedCard?.dataset.studentAnswerId;

        if (!studentAnswerId) {
            console.error("Student answer ID not found");
            return;
        }

        fetch(this.testData.routes.submitAnswer, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": this.testData.csrfToken,
            },
            body: JSON.stringify({
                student_answer_id: studentAnswerId,
                answer_id: answerId,
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    this.handleAnswerResult(questionId, submitBtn);
                } else {
                    this.showAlert(
                        this.translations.errorTitle,
                        data.message || this.translations.errorOccurred,
                        "error"
                    );
                }
            })
            .catch(() => {
                this.showAlert(
                    this.translations.errorTitle,
                    this.translations.serverError,
                    "error"
                );
            });
    },

    // Handle answer submission result
    handleAnswerResult(questionId, submitBtn) {
        if (this.testFinished) return;

        this.answeredQuestions.add(questionId);

        const navBtn = document.getElementById(`navBtn${questionId}`);
        if (navBtn) {
            navBtn.classList.remove("current");
            if (!navBtn.classList.contains("answered")) {
                navBtn.classList.add("answered");
            }

            if (!navBtn.dataset.completed) {
                this.completedQuestions++;
                navBtn.dataset.completed = "true";
                this.updateProgress();
            }
        }

        const questionContainer = document.getElementById(
            `question-${questionId}`
        );
        if (questionContainer) {
            questionContainer
                .querySelectorAll(".variant-card")
                .forEach((variant) => {
                    variant.style.pointerEvents = "none";
                    variant.classList.add("disabled");
                });
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<i class="icofont icofont-check me-1"></i><small>Javob berildi</small>`;
        }

        setTimeout(() => {
            if (this.completedQuestions >= this.questions.length) {
                clearInterval(this.timerInterval);
                this.isTimerActive = false;
                this.finishTest();
            } else if (this.currentQuestionIndex < this.questions.length - 1) {
                this.currentQuestionIndex++;
                const nextQuestionId =
                    this.questions[this.currentQuestionIndex].id;
                this.showQuestion(
                    nextQuestionId,
                    this.currentQuestionIndex + 1
                );
            }
        }, 1000);
    },

    // Update progress display
    updateProgress() {
        const completedElement = document.getElementById("completedCount");
        const progressBar = document.getElementById("progressBar");

        if (completedElement)
            completedElement.textContent = this.completedQuestions;
        if (progressBar) {
            const percentage =
                this.questions.length > 0
                    ? (this.completedQuestions / this.questions.length) * 100
                    : 0;
            progressBar.style.width = percentage + "%";
            progressBar.setAttribute("aria-valuenow", this.completedQuestions);
        }
    },

    // Show question
    showQuestion(questionId, questionNumber) {
        if (this.testFinished) return;

        // Hide all questions
        document
            .querySelectorAll(".question-container")
            .forEach((q) => q.classList.remove("active"));

        // Show target question
        const targetQuestion = document.getElementById(
            `question-${questionId}`
        );
        if (targetQuestion) {
            targetQuestion.classList.add("active");

            // Render MathJax for the newly visible question
            this.renderMathJax([targetQuestion]);
        }

        // Update navigation buttons
        document
            .querySelectorAll(".nav-btn")
            .forEach((btn) => btn.classList.remove("current"));

        const navBtn = document.getElementById(`navBtn${questionId}`);
        if (navBtn && !navBtn.classList.contains("answered")) {
            navBtn.classList.add("current");
        }

        this.currentQuestionId = questionId;

        // Update current question display
        const currentQuestionElement =
            document.getElementById("currentQuestion");
        if (currentQuestionElement) {
            currentQuestionElement.textContent = questionNumber;
        }

        // Restore selected answer if exists
        if (this.selectedAnswers[questionId]) {
            const selectedAnswerId = this.selectedAnswers[questionId];
            const questionContainer = document.getElementById(
                `question-${questionId}`
            );
            if (questionContainer) {
                const selectedCard = questionContainer.querySelector(
                    `[data-answer-id="${selectedAnswerId}"]`
                );
                if (selectedCard) {
                    selectedCard.classList.add("selected");
                    const radioInput = questionContainer.querySelector(
                        `input[value="${selectedAnswerId}"]`
                    );
                    if (radioInput) radioInput.checked = true;
                }
            }
        }
    },

    // Finish test
    finishTest(isAutomatic = false) {
        if (this.testFinished && !isAutomatic) return;
        if (!isAutomatic && !this.testFinished) this.testFinished = true;

        clearInterval(this.timerInterval);
        this.isTimerActive = false;

        document.querySelectorAll(".nav-btn").forEach((btn) => {
            btn.disabled = true;
            btn.style.pointerEvents = "none";
        });

        if (
            !this.testData ||
            !this.testData.routes ||
            !this.testData.routes.finish
        ) {
            this.showAlert(
                this.translations.errorTitle,
                "Test yakunlash uchun yo'l topilmadi!",
                "error",
                () => (window.location.href = "/student/home")
            );
            return;
        }

        fetch(this.testData.routes.finish, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": this.testData.csrfToken || "",
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
            body: JSON.stringify({}),
        })
            .then((response) => {
                if (!response.ok) {
                    return response.json().then((err) => {
                        throw new Error(err.message || "Server xatosi");
                    });
                }
                return response.json();
            })
            .then((data) => {
                if (data.success) {
                    this.showResultsModal(data);
                } else {
                    this.showAlert(
                        this.translations.errorTitle,
                        data.message || this.translations.errorOccurred,
                        "error",
                        () => {
                            if (data.redirect_url) {
                                window.location.href = data.redirect_url;
                            }
                        }
                    );
                }
            })
            .catch((error) => {
                console.error("Finish test error:", error);
                this.showAlert(
                    this.translations.errorTitle,
                    error.message || this.translations.serverError,
                    "error",
                    () => (window.location.href = "/student/home")
                );
            });
    },

    // Show results modal
    showResultsModal(data) {
        const totalAnswered = this.completedQuestions;
        const usedMinutes = Math.floor(
            (this.totalTimeInSeconds - this.remainingTime) / 60
        );
        const usedSeconds = (this.totalTimeInSeconds - this.remainingTime) % 60;

        this.showAlert(
            this.translations.testFinished,
            `
                <div class="text-start">
                    <p><strong>${
                        this.translations.answeredQuestions
                    }:</strong> ${totalAnswered} / ${this.questions.length}</p>
                    <p><strong>${this.translations.correctAnswers}:</strong> ${
                data.correct_answers || 0
            }</p>
                    <p><strong>${this.translations.score}:</strong> ${
                data.score || 0
            }%</p>
                    <p><strong>${
                        this.translations.timeUsed
                    }:</strong> ${usedMinutes}:${usedSeconds
                .toString()
                .padStart(2, "0")}</p>
                </div>
            `,
            "success",
            () => {
                if (this.testData.routes && this.testData.routes.logout) {
                    const form = document.createElement("form");
                    form.method = "POST";
                    form.action = this.testData.routes.logout;

                    const csrfInput = document.createElement("input");
                    csrfInput.type = "hidden";
                    csrfInput.name = "_token";
                    csrfInput.value = this.testData.csrfToken;
                    form.appendChild(csrfInput);

                    document.body.appendChild(form);
                    form.submit();
                } else {
                    window.location.href = "/student/home";
                }
            }
        );
    },

    // Render MathJax
    renderMathJax(elements = null) {
        if (window.MathJax && window.MathJax.typesetPromise) {
            window.MathJax.typesetPromise(elements).catch((err) => {
                console.error("MathJax render error:", err);
            });
        }
    },

    // Show alert (using SweetAlert if available, otherwise native alert)
    showAlert(title, text, icon = "info", callback = null) {
        if (typeof Swal !== "undefined") {
            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                confirmButtonText: "OK",
                allowOutsideClick: false,
            }).then(() => {
                if (callback) callback();
            });
        } else {
            alert(`${title}: ${text}`);
            if (callback) callback();
        }
    },

    // Show confirm dialog
    showConfirmDialog(
        title,
        text,
        icon = "question",
        confirmCallback = null,
        cancelCallback = null
    ) {
        if (typeof Swal !== "undefined") {
            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#6c757d",
                confirmButtonText: this.translations.yesFinish || "Yes",
                cancelButtonText: this.translations.cancel || "Cancel",
            }).then((result) => {
                if (result.isConfirmed && confirmCallback) {
                    confirmCallback();
                } else if (!result.isConfirmed && cancelCallback) {
                    cancelCallback();
                }
            });
        } else {
            if (confirm(`${title}: ${text}`)) {
                if (confirmCallback) confirmCallback();
            } else {
                if (cancelCallback) cancelCallback();
            }
        }
    },
};

// Initialize when document is ready
document.addEventListener("DOMContentLoaded", function () {
    TestManager.init();

    // Initial MathJax render for all visible content
    setTimeout(() => {
        TestManager.renderMathJax();
    }, 1000);
});

// Make TestManager available globally for debugging
window.TestManager = TestManager;

