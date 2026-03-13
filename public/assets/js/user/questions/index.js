// import Swal from "sweetalert2";
// import "sweetalert2/dist/sweetalert2.min.css";

document.addEventListener("DOMContentLoaded", function () {
    let questions = window.questions;
    let currentQuestionId = questions[0].id;
    let selectedAnswers = {};
    let completedQuestions = 0;

    let totalTimeInSeconds = 1 * 60; // 20 daqiqa
    let remainingTime = totalTimeInSeconds;
    let timerInterval;
    let isTimerActive = true;

    function startTimer() {
        const timerDisplay = document.getElementById("timerDisplay");
        const progressCircle = document.getElementById("progressCircle");
        const circumference = 2 * Math.PI * 35; // r=35

        timerInterval = setInterval(function () {
            if (!isTimerActive) return;

            remainingTime--;

            const minutes = Math.floor(remainingTime / 60);
            const seconds = remainingTime % 60;
            timerDisplay.textContent = `${minutes}:${seconds
                .toString()
                .padStart(2, "0")}`;

            const progress =
                (totalTimeInSeconds - remainingTime) / totalTimeInSeconds;
            const offset = circumference * progress;
            progressCircle.style.strokeDashoffset = offset;

            if (remainingTime <= 300) {
                progressCircle.style.stroke = "#ef4444"; // qizil
                timerDisplay.style.color = "#ef4444";
            } else if (remainingTime <= 600) {
                progressCircle.style.stroke = "#f59e0b"; // sariq
                timerDisplay.style.color = "#f59e0b";
            }

            if (remainingTime <= 0) {
                clearInterval(timerInterval);
                timeUp();
            }
        }, 1000);
    }

    function timeUp() {
        isTimerActive = false;

        document.querySelectorAll(".variant-card").forEach((card) => {
            card.style.pointerEvents = "none";
            card.classList.add("disabled");
        });

        document.querySelectorAll(".submit-btn").forEach((btn) => {
            btn.disabled = true;
        });

        Swal.fire({
            title: "Vaqt tugadi!",
            text: "Test vaqti yakunlandi. Natijalaringiz hisoblanmoqda...",
            icon: "warning",
            confirmButtonText: "OK",
            allowOutsideClick: false,
        }).then(() => {
            finishTest();
        });
    }

    startTimer();

    document.querySelectorAll(".variant-card").forEach(function (card) {
        card.addEventListener("click", function (e) {
            if (!isTimerActive) return; // Vaqt tugagan bo'lsa ishlamaydi

            e.preventDefault();
            e.stopPropagation();

            const answerId = parseInt(this.dataset.answerId);
            const questionId = parseInt(this.dataset.questionId);

            const questionContainer = document.getElementById(
                `question-${questionId}`
            );
            const allVariants =
                questionContainer.querySelectorAll(".variant-card");

            allVariants.forEach((variant) =>
                variant.classList.remove("selected")
            );

            this.classList.add("selected");

            const radioInput = questionContainer.querySelector(
                `input[value="${answerId}"]`
            );
            if (radioInput) {
                const allRadios = questionContainer.querySelectorAll(
                    'input[type="radio"]'
                );
                allRadios.forEach((radio) => (radio.checked = false));
                radioInput.checked = true;
            }

            selectedAnswers[questionId] = answerId;

            const submitBtn = document.getElementById(`submitBtn${questionId}`);
            if (submitBtn) {
                submitBtn.disabled = false;
            }

            this.style.animation = "pulse 0.6s ease-in-out";
        });
    });

    document.querySelectorAll(".submit-btn").forEach(function (btn) {
        btn.addEventListener("click", function (e) {
            if (!isTimerActive) return; // Vaqt tugagan bo'lsa ishlamaydi

            e.preventDefault();

            const questionId = parseInt(this.dataset.questionId);
            const selectedAnswerId = selectedAnswers[questionId];

            if (!selectedAnswerId) {
                alert("Iltimos, javobni tanlang!");
                return;
            }

            const questionContainer = document.getElementById(
                `question-${questionId}`
            );
            const selectedCard = questionContainer.querySelector(
                `[data-answer-id="${selectedAnswerId}"]`
            );
            const isCorrect = selectedCard.dataset.isCorrect === "true";

            const navBtn = document.getElementById(`navBtn${questionId}`);
            if (navBtn) {
                navBtn.classList.remove("current");
                if (isCorrect) {
                    navBtn.classList.remove("incorrect");
                    navBtn.classList.add("correct");
                } else {
                    navBtn.classList.remove("correct");
                    navBtn.classList.add("incorrect");
                }

                if (!navBtn.dataset.completed) {
                    completedQuestions++;
                    navBtn.dataset.completed = "true";
                    document.getElementById("completedCount").textContent =
                        completedQuestions;
                }
            }

            const allVariants =
                questionContainer.querySelectorAll(".variant-card");
            allVariants.forEach((variant) => {
                variant.style.pointerEvents = "none";
                variant.classList.add("disabled");
            });

            this.disabled = true;
            this.innerHTML = isCorrect
                ? "<i class=\"icofont icofont-check me-2\"></i>To'g'ri javob!"
                : "<i class=\"icofont icofont-close me-2\"></i>Noto'g'ri javob";

            setTimeout(() => {
                const allQuestions = Array.from(
                    document.querySelectorAll(".question-container")
                );
                const currentIndex = allQuestions.findIndex((q) =>
                    q.classList.contains("active")
                );

                if (completedQuestions < allQuestions.length) {
                    if (currentIndex < allQuestions.length - 1) {
                        const nextQuestion = allQuestions[currentIndex + 1];
                        const nextQuestionId = parseInt(
                            nextQuestion.id.replace("question-", "")
                        );
                        showQuestion(nextQuestionId, currentIndex + 2);
                    }
                } else {
                    clearInterval(timerInterval);
                    isTimerActive = false;
                    finishTest();
                }
            }, 500);
        });
    });

    function finishTest() {
        let totalQuestions = Object.keys(selectedAnswers).length;
        let correctCount = 0;
        let incorrectCount = 0;

        for (let questionId in selectedAnswers) {
            const answerId = selectedAnswers[questionId];
            const selectedCard = document.querySelector(
                `[data-question-id="${questionId}"][data-answer-id="${answerId}"]`
            );

            if (selectedCard && selectedCard.dataset.isCorrect === "true") {
                correctCount++;
            } else {
                incorrectCount++;
            }
        }

        const usedMinutes = Math.floor(
            (totalTimeInSeconds - remainingTime) / 60
        );
        const usedSeconds = (totalTimeInSeconds - remainingTime) % 60;

        Swal.fire({
            title: "Test yakunlandi!",
            html: `
        <p style="font-size:18px;"><b>Jami savollar:</b> ${totalQuestions}</p>
        <p style="color:green; font-size:18px;"><b>To'g'ri javoblar:</b> ${correctCount}</p>
        <p style="color:red; font-size:18px;"><b>Noto'g'ri javoblar:</b> ${incorrectCount}</p>
        <p style="color:#6366f1; font-size:16px;"><b>Sarflangan vaqt:</b> ${usedMinutes}:${usedSeconds
                .toString()
                .padStart(2, "0")}</p>
    `,
            icon: "success",
            confirmButtonText: "OK",
        });
    }

    document.querySelectorAll(".nav-btn").forEach(function (btn) {
        btn.addEventListener("click", function (e) {
            if (!isTimerActive) return; // Vaqt tugagan bo'lsa ishlamaydi

            e.preventDefault();

            const questionId = parseInt(this.dataset.questionId);
            const questionNumber = parseInt(this.dataset.questionNumber);

            showQuestion(questionId, questionNumber);
        });
    });

    function showQuestion(questionId, questionNumber) {
        document.querySelectorAll(".question-container").forEach((question) => {
            question.classList.remove("active");
        });

        const targetQuestion = document.getElementById(
            `question-${questionId}`
        );
        if (targetQuestion) {
            targetQuestion.classList.add("active");
        }

        document.querySelectorAll(".nav-btn").forEach((btn) => {
            btn.classList.remove("current");
        });

        const navBtn = document.getElementById(`navBtn${questionId}`);
        if (
            navBtn &&
            !navBtn.classList.contains("correct") &&
            !navBtn.classList.contains("incorrect")
        ) {
            navBtn.classList.add("current");
        }

        currentQuestionId = questionId;
        document.getElementById("currentQuestion").textContent = questionNumber;
    }
});
