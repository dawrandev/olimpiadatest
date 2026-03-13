(function() {
    'use strict';
    
    // 1. DevTools detection
    let devtoolsOpen = false;
    const threshold = 160;
    
    setInterval(() => {
        const widthDiff = window.outerWidth - window.innerWidth;
        const heightDiff = window.outerHeight - window.innerHeight;
        
        if (widthDiff > threshold || heightDiff > threshold) {
            if (!devtoolsOpen) {
                devtoolsOpen = true;
                
                Swal.fire({
                    title: 'Ogohlantirish!',
                    text: 'Developer tools aniqlandi. Test avtomatik yakunlanmoqda.',
                    icon: 'error',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    if (typeof window.finishTest === 'function') {
                        window.finishTest(true);
                    } else {
                        window.location.href = '/student/home';
                    }
                });
            }
        } else {
            devtoolsOpen = false;
        }
    }, 500);
    
    document.addEventListener('contextmenu', e => e.preventDefault());
    
    document.addEventListener('keydown', function(e) {
        // F12, Ctrl+Shift+I, Ctrl+U, Ctrl+Shift+C
        if (e.keyCode === 123 || 
            (e.ctrlKey && e.shiftKey && e.keyCode === 73) ||
            (e.ctrlKey && e.keyCode === 85) ||
            (e.ctrlKey && e.shiftKey && e.keyCode === 67)) {
            e.preventDefault();
            return false;
        }
    });
    
    document.addEventListener('keyup', function(e) {
        if (e.key === 'PrintScreen') {
            // Log qilish (optional)
            if (window.testData && window.testData.csrfToken) {
                fetch('/api/log-screenshot-attempt', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.testData.csrfToken
                    },
                    body: JSON.stringify({
                        student_id: window.testData.studentId,
                        test_id: window.testData.testId,
                        timestamp: new Date().toISOString()
                    })
                }).catch(err => console.log('Log failed'));
            }
        }
    });
    
    let blurCount = 0;
    const MAX_BLUR = 3; 
    let lastBlurTime = 0;
    
    window.addEventListener('blur', function() {
        const now = Date.now();
        
        if (now - lastBlurTime < 10000) {
            blurCount++;
        } else {
            blurCount = 1; // Reset
        }
        
        lastBlurTime = now;
        
        if (blurCount >= MAX_BLUR) {
            Swal.fire({
                title: 'Ogohlantirish!',
                text: `Siz ${MAX_BLUR} marta test oynasidan chiqib ketdingiz. Test yakunlanmoqda.`,
                icon: 'warning',
                allowOutsideClick: false,
                showConfirmButton: false,
                timer: 3000
            }).then(() => {
                if (typeof window.finishTest === 'function') {
                    window.finishTest(true);
                } else {
                    window.location.href = '/student/home';
                }
            });
        } else if (blurCount >= 1) {
            Swal.fire({
                title: 'Diqqat!',
                text: `Test oynasidan ${blurCount} marta chiqdingiz. ${MAX_BLUR - blurCount} ta imkoniyat qoldi.`,
                icon: 'info',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
    
    document.addEventListener('copy', e => e.preventDefault());
    document.addEventListener('cut', e => e.preventDefault());
    document.addEventListener('paste', e => e.preventDefault());
    
    console.log('🔒 Security protection activated');
    
})();
document.addEventListener("DOMContentLoaded", function () {
    const questions = window.questions || [];
    const testData = window.testData || {};

    const currentLocale =
        window.appLocale || document.documentElement.lang || "uz";

    const allTranslations = {
        uz: {
            timeUpTitle: "Vaqt tugadi!",
            timeUpText: "Test avtomatik yakunlanmoqda...",
            warningTitle: "Ogohlantirish!",
            selectAnswer: "Iltimos javob tanlang!",
            enterSequence: "Iltimos ketma-ketlik kiriting!",
            enterMatching: "Iltimos barcha juftliklarni tanlang!",
            invalidSequence:
                "Noto'g'ri ketma-ketlik! 1 dan N gacha bo'lgan raqamlarni takrorlanmay kiriting.",
            errorTitle: "Xato!",
            errorOccurred: "Xatolik yuz berdi",
            serverError: "Serverga ulanib bo'lmadi",
            finishTitle: "Testni yakunlash",
            finishText: "Testni yakunlamoqchimisiz?",
            yesFinish: "Ha, yakunlash",
            cancel: "Bekor qilish",
            testFinished: "Test yakunlandi!",
            answeredQuestions: "Javob berilgan savollar",
            correctAnswers: "To'g'ri javoblar",
            score: "Ball",
            timeUsed: "Sarflangan vaqt",
            logout: "Chiqish",
            cannotSelectAll: "Barcha variantlarni tanlab bo'lmaydi!",
            answerSubmitted: "Javob berildi",
            ok: "OK",
        },
        ru: {
            timeUpTitle: "Время вышло!",
            timeUpText: "Тест автоматически завершается...",
            warningTitle: "Предупреждение!",
            selectAnswer: "Пожалуйста, выберите ответ!",
            enterSequence: "Пожалуйста, введите последовательность!",
            enterMatching: "Пожалуйста, выберите все пары!",
            invalidSequence:
                "Неверная последовательность! Введите числа от 1 до N без повторов.",
            errorTitle: "Ошибка!",
            errorOccurred: "Произошла ошибка",
            serverError: "Не удалось подключиться к серверу",
            finishTitle: "Завершение теста",
            finishText: "Вы уверены, что хотите завершить тест?",
            yesFinish: "Да, завершить",
            cancel: "Отмена",
            testFinished: "Тест завершён!",
            answeredQuestions: "Отвеченные вопросы",
            correctAnswers: "Правильные ответы",
            score: "Баллы",
            timeUsed: "Затраченное время",
            logout: "Выйти",
            cannotSelectAll: "Невозможно выбрать все варианты!",
            answerSubmitted: "Ответ отправлен",
            ok: "OK",
        },
        kk: {
            timeUpTitle: "Waqıt tawsıldı!",
            timeUpText: "Test avtomatik túrde juwmaqlanbaqta...",
            warningTitle: "Eskertiw!",
            selectAnswer: "Iltimas juwap tańlań!",
            enterSequence: "Iltimas izbe-izlik kirgiziń!",
            enterMatching: "Barlıq juplıqlardı tańlań.!",
            invalidSequence:
                "Naduris izbe-izlik! 1 den N ge shekem bolgan cifrlardı tákirarlanbay kirgiziń..",
            errorTitle: "Qáte!",
            errorOccurred: "Qáte júz berdi",
            serverError: "Serverge jalǵana almadı",
            finishTitle: "Testti juwmaqlaw",
            finishText: "Testti juwmaqlamaqshısız ba??",
            yesFinish: "Awa, juwmaqlaw",
            cancel: "Biykarlaw",
            testFinished: "Test juwmaqlandı!",
            answeredQuestions: "Javob berilgan savollar",
            correctAnswers: "Durıs juwaplar",
            score: "Ball",
            timeUsed: "Sarıplanǵan waqıt",
            logout: "Shıǵıw",
            cannotSelectAll: "Barlıq variantlardı tańlap bolmaydı!",
            answerSubmitted: "Juwap berildi",
            ok: "OK",
        },
    };

    const defaultTranslations =
        allTranslations[currentLocale] || allTranslations.uz;

    const translations = window.translations || defaultTranslations;

    function t(key) {
        if (
            window.translations &&
            typeof window.translations[key] !== "undefined"
        ) {
            return window.translations[key];
        }
        if (translations && typeof translations[key] !== "undefined") {
            return translations[key];
        }
        return defaultTranslations[key] || key;
    }

    let currentQuestionId = questions.length > 0 ? questions[0].id : 0;
    let currentQuestionIndex = 0;
    let selectedAnswers = {};
    let matchingAnswers = {};
    let sequenceAnswers = {};
    let completedQuestions = 0;
    let answeredQuestions = new Set();

    const totalSeconds = testData.totalSeconds || 600;
    const startedAt = testData.startedAt || Math.floor(Date.now() / 1000);

    const now = Math.floor(Date.now() / 1000);
    const elapsedSeconds = now - startedAt;
    let remainingTime = Math.max(0, totalSeconds - elapsedSeconds);

    let timerInterval;
    let isTimerActive = true;
    let testFinished = false;

    function loadAnsweredQuestions() {
        questions.forEach((question) => {
            if (question.is_answered) {
                const questionId = question.id;
                const navBtn = document.getElementById(`navBtn${questionId}`);
                const submitBtn = document.getElementById(
                    `submitBtn${questionId}`
                );
                const questionContainer = document.getElementById(
                    `question-${questionId}`
                );

                if (navBtn) {
                    navBtn.classList.add("answered");
                    if (!navBtn.dataset.completed) {
                        completedQuestions++;
                        navBtn.dataset.completed = "true";
                    }
                }

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `<i class="icofont icofont-check me-1"></i><small>${t(
                        "answerSubmitted"
                    )}</small>`;
                }

                if (questionContainer) {
                    questionContainer
                        .querySelectorAll(".variant-card")
                        .forEach((variant) => {
                            variant.style.pointerEvents = "none";
                            variant.classList.add("disabled");
                        });
                    questionContainer
                        .querySelectorAll(".matching-select")
                        .forEach((select) => (select.disabled = true));
                    questionContainer
                        .querySelectorAll(".sequence-input")
                        .forEach((input) => (input.disabled = true));
                }

                answeredQuestions.add(questionId);

                if (question.type === "single_choice") {
                    if (
                        question.selected_answer_text &&
                        question.selected_answer_text.includes(",")
                    ) {
                        const answerIds = question.selected_answer_text
                            .split(",")
                            .map((id) => parseInt(id.trim()));
                        selectedAnswers[questionId] = answerIds;
                        answerIds.forEach((answerId) => {
                            const card = questionContainer.querySelector(
                                `[data-answer-id="${answerId}"]`
                            );
                            if (card) card.classList.add("selected");
                        });
                    } else if (question.selected_answer_id) {
                        selectedAnswers[questionId] =
                            question.selected_answer_id;
                        const card = questionContainer.querySelector(
                            `[data-answer-id="${question.selected_answer_id}"]`
                        );
                        if (card) card.classList.add("selected");
                        const radioInput = questionContainer.querySelector(
                            `input[value="${question.selected_answer_id}"]`
                        );
                        if (radioInput) radioInput.checked = true;
                    }
                }

                if (
                    question.type === "matching" &&
                    question.selected_answer_text
                ) {
                    const pairs = question.selected_answer_text.split(",");
                    matchingAnswers[questionId] = {};
                    pairs.forEach((pair) => {
                        const [left, right] = pair.split("-");
                        if (left && right) {
                            matchingAnswers[questionId][left.trim()] =
                                right.trim();
                            const select = questionContainer.querySelector(
                                `[data-left-key="${left.trim()}"][data-question-id="${questionId}"]`
                            );
                            if (select) select.value = right.trim();
                        }
                    });
                }

                if (
                    question.type === "sequence" &&
                    question.selected_answer_text
                ) {
                    const answerIds = question.selected_answer_text
                        .split(",")
                        .map((id) => id.trim());
                    sequenceAnswers[questionId] = {};
                    answerIds.forEach((answerId, index) => {
                        sequenceAnswers[questionId][index] = answerId;
                        const input = questionContainer.querySelector(
                            `[data-position="${index}"][data-question-id="${questionId}"]`
                        );
                        if (input) input.value = answerId;
                    });
                }
            }
        });

        updateProgress();
    }

    function startTimer() {
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

        timerDisplay.textContent = formatTime(remainingTime);

        timerInterval = setInterval(function () {
            if (!isTimerActive || testFinished) return;

            remainingTime--;
            if (remainingTime < 0) remainingTime = 0;

            timerDisplay.textContent = formatTime(remainingTime);

            if (progressCircle && circumference > 0) {
                const elapsed = totalSeconds - remainingTime;
                const progress = elapsed / totalSeconds;
                const offset = circumference * progress;
                progressCircle.style.strokeDashoffset = offset;

                if (remainingTime <= 300) {
                    progressCircle.style.stroke = "#ef4444";
                    timerDisplay.style.color = "#ef4444";
                } else if (remainingTime <= 600) {
                    progressCircle.style.stroke = "#f59e0b";
                    timerDisplay.style.color = "#f59e0b";
                }
            }

            if (remainingTime <= 0) {
                clearInterval(timerInterval);
                timeUp();
            }
        }, 1000);
    }

    function formatTime(time) {
        const minutes = Math.floor(time / 60);
        let seconds = time % 60;
        if (seconds < 10) seconds = "0" + seconds;
        return `${minutes}:${seconds}`;
    }

    function timeUp() {
        if (testFinished) return;
        isTimerActive = false;
        testFinished = true;

        document.querySelectorAll(".variant-card").forEach((card) => {
            card.style.pointerEvents = "none";
            card.classList.add("disabled");
        });
        document
            .querySelectorAll(".matching-select")
            .forEach((select) => (select.disabled = true));
        document
            .querySelectorAll(".sequence-input")
            .forEach((input) => (input.disabled = true));
        document
            .querySelectorAll(".submit-btn")
            .forEach((btn) => (btn.disabled = true));

        Swal.fire({
            title: t("timeUpTitle"),
            text: t("timeUpText"),
            icon: "warning",
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false,
            allowOutsideClick: false,
        }).then(() => finishTest(true));
    }

    function setupNavigationButtons() {
        const navButtons = document.querySelectorAll(".nav-btn");
        navButtons.forEach((btn) => {
            btn.addEventListener("click", function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (testFinished) return;

                const questionId = parseInt(this.dataset.questionId, 10);
                const questionIndex = questions.findIndex(
                    (q) => q.id === questionId
                );

                if (questionIndex !== -1) {
                    currentQuestionIndex = questionIndex;
                    showQuestion(questionId, questionIndex + 1);
                }
            });
        });
    }

    function setupVariantCards() {
        const variantCards = document.querySelectorAll(".variant-card");
        variantCards.forEach((card) => {
            card.addEventListener("click", function (e) {
                if (!isTimerActive || testFinished) return;
                if (
                    this.classList.contains("disabled") ||
                    this.style.pointerEvents === "none"
                )
                    return;

                e.preventDefault();
                e.stopPropagation();

                const answerId = parseInt(this.dataset.answerId, 10);
                const questionId = parseInt(this.dataset.questionId, 10);
                const questionContainer = document.getElementById(
                    `question-${questionId}`
                );
                if (!questionContainer) return;

                const question = questions.find((q) => q.id === questionId);
                const isMultiple = question?.is_multiple || false;
                const allVariants =
                    questionContainer.querySelectorAll(".variant-card");
                const totalVariants = allVariants.length;

                if (isMultiple) {
                    if (!selectedAnswers[questionId])
                        selectedAnswers[questionId] = [];

                    const currentSelected = selectedAnswers[questionId];
                    const isCurrentlySelected =
                        currentSelected.includes(answerId);

                    if (isCurrentlySelected) {
                        selectedAnswers[questionId] = currentSelected.filter(
                            (id) => id !== answerId
                        );
                        this.classList.remove("selected");
                    } else {
                        if (currentSelected.length >= totalVariants - 1) {
                            Swal.fire({
                                title: t("warningTitle"),
                                text: t("cannotSelectAll"),
                                icon: "warning",
                                confirmButtonText: t("ok"),
                                timer: 2000,
                            });
                            return;
                        }
                        selectedAnswers[questionId].push(answerId);
                        this.classList.add("selected");
                    }
                } else {
                    allVariants.forEach((variant) =>
                        variant.classList.remove("selected")
                    );
                    this.classList.add("selected");

                    const radioInput = questionContainer.querySelector(
                        `input[value="${answerId}"]`
                    );
                    if (radioInput) {
                        questionContainer
                            .querySelectorAll('input[type="radio"]')
                            .forEach((radio) => (radio.checked = false));
                        radioInput.checked = true;
                    }
                    selectedAnswers[questionId] = answerId;
                }

                const submitBtn = document.getElementById(
                    `submitBtn${questionId}`
                );
                if (submitBtn) {
                    submitBtn.disabled = isMultiple
                        ? selectedAnswers[questionId].length === 0
                        : !selectedAnswers[questionId];
                }

                this.style.animation = "pulse 0.6s ease-in-out";
                setTimeout(() => (this.style.animation = ""), 600);
            });
        });
    }

    function setupMatchingSelects() {
        const matchingSelects = document.querySelectorAll(".matching-select");
        matchingSelects.forEach((select) => {
            select.addEventListener("change", function () {
                if (!isTimerActive || testFinished) return;

                const questionId = parseInt(this.dataset.questionId, 10);
                const leftKey = this.dataset.leftKey;
                const rightKey = this.value;

                if (!matchingAnswers[questionId])
                    matchingAnswers[questionId] = {};

                if (rightKey) {
                    matchingAnswers[questionId][leftKey] = rightKey;
                } else {
                    delete matchingAnswers[questionId][leftKey];
                }

                const questionContainer = document.getElementById(
                    `question-${questionId}`
                );
                const allSelects =
                    questionContainer.querySelectorAll(".matching-select");
                const allFilled = Array.from(allSelects).every(
                    (sel) => sel.value !== ""
                );

                const submitBtn = document.getElementById(
                    `submitBtn${questionId}`
                );
                if (submitBtn) submitBtn.disabled = !allFilled;
            });
        });
    }

    function setupSequenceInputs() {
        const sequenceInputs = document.querySelectorAll(".sequence-input");
        sequenceInputs.forEach((input) => {
            input.addEventListener("change", function () {
                if (!isTimerActive || testFinished) return;

                const questionId = parseInt(this.dataset.questionId, 10);
                const position = parseInt(this.dataset.position, 10);
                const selectedIndex = this.value;

                if (!sequenceAnswers[questionId])
                    sequenceAnswers[questionId] = {};
                sequenceAnswers[questionId][position] =
                    selectedIndex !== "" ? selectedIndex : null;

                const submitBtn = document.getElementById(
                    `submitBtn${questionId}`
                );
                if (submitBtn) {
                    const allInputs = document.querySelectorAll(
                        `[data-question-id="${questionId}"].sequence-input`
                    );
                    const hasAllAnswers = Array.from(allInputs).every(
                        (inp) => inp.value !== ""
                    );

                    const selectedIndexes = Object.values(
                        sequenceAnswers[questionId]
                    ).filter((v) => v !== null && v !== "");
                    const uniqueIndexes = [...new Set(selectedIndexes)];
                    const hasDuplicates =
                        selectedIndexes.length !== uniqueIndexes.length;

                    submitBtn.disabled = !hasAllAnswers || hasDuplicates;
                }
            });
        });
    }

    function setupSubmitButtons() {
        const submitBtns = document.querySelectorAll(".submit-btn");
        submitBtns.forEach((btn) => {
            btn.addEventListener("click", function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (!isTimerActive || testFinished) return;

                const questionId = parseInt(this.dataset.questionId, 10);
                const question = questions.find((q) => q.id === questionId);
                if (!question) return;

                if (question.type === "single_choice") {
                    const selectedAnswerIds = selectedAnswers[questionId];
                    if (!selectedAnswerIds || selectedAnswerIds.length === 0) {
                        Swal.fire({
                            title: t("warningTitle"),
                            text: t("selectAnswer"),
                            icon: "warning",
                            confirmButtonText: t("ok"),
                        });
                        return;
                    }
                    submitSingleChoice(questionId, selectedAnswerIds, this);
                } else if (question.type === "matching") {
                    const pairs = matchingAnswers[questionId] || {};
                    if (Object.keys(pairs).length === 0) {
                        Swal.fire({
                            title: t("warningTitle"),
                            text: t("enterMatching"),
                            icon: "warning",
                            confirmButtonText: t("ok"),
                        });
                        return;
                    }
                    submitMatching(questionId, pairs, this);
                } else if (question.type === "sequence") {
                    const sequence = sequenceAnswers[questionId] || {};
                    if (Object.keys(sequence).length === 0) {
                        Swal.fire({
                            title: t("warningTitle"),
                            text: t("enterSequence"),
                            icon: "warning",
                            confirmButtonText: t("ok"),
                        });
                        return;
                    }
                    submitSequence(questionId, sequence, this);
                }
            });
        });
    }

    function getStudentAnswerId(questionId) {
        const input = document.getElementById(`studentAnswerId${questionId}`);
        return input ? input.value : "";
    }

    function submitSingleChoice(questionId, answerData, submitBtn) {
        const studentAnswerId = getStudentAnswerId(questionId);
        if (!studentAnswerId) {
            Swal.fire({
                title: t("errorTitle"),
                text: "Student answer ID not found",
                icon: "error",
                confirmButtonText: t("ok"),
            });
            return;
        }

        const question = questions.find((q) => q.id === questionId);
        const isMultiple = question?.is_multiple || false;

        let requestBody = {
            student_answer_id: studentAnswerId,
            question_type: "single_choice",
        };

        if (isMultiple) {
            requestBody.answer_ids = Array.isArray(answerData)
                ? answerData.join(",")
                : String(answerData);
        } else {
            requestBody.answer_id = answerData;
        }

        fetch(testData.routes.submitAnswer, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": testData.csrfToken,
            },
            body: JSON.stringify(requestBody),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    handleAnswerResult(questionId, submitBtn);
                } else {
                    Swal.fire({
                        title: t("errorTitle"),
                        text: data.message || t("errorOccurred"),
                        icon: "error",
                        confirmButtonText: t("ok"),
                    });
                }
            })
            .catch((error) => {
                Swal.fire({
                    title: t("errorTitle"),
                    text: t("serverError"),
                    icon: "error",
                    confirmButtonText: t("ok"),
                });
            });
    }

    function submitMatching(questionId, pairs, submitBtn) {
        const studentAnswerId = getStudentAnswerId(questionId);
        if (!studentAnswerId) return;

        const answerText = Object.entries(pairs)
            .map(([left, right]) => `${left}-${right}`)
            .sort()
            .join(",");

        fetch(testData.routes.submitAnswer, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": testData.csrfToken,
            },
            body: JSON.stringify({
                student_answer_id: studentAnswerId,
                answer_text: answerText,
                question_type: "matching",
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    handleAnswerResult(questionId, submitBtn);
                } else {
                    Swal.fire({
                        title: t("errorTitle"),
                        text: data.message || t("errorOccurred"),
                        icon: "error",
                        confirmButtonText: t("ok"),
                    });
                }
            })
            .catch((error) => {
                Swal.fire({
                    title: t("errorTitle"),
                    text: t("serverError"),
                    icon: "error",
                    confirmButtonText: t("ok"),
                });
            });
    }

    function submitSequence(questionId, sequence, submitBtn) {
        const studentAnswerId = getStudentAnswerId(questionId);

        if (!studentAnswerId) return;

        const answerText = Object.keys(sequence)
            .sort((a, b) => parseInt(a) - parseInt(b))
            .map((key) => sequence[key])
            .join(",");

        fetch(testData.routes.submitAnswer, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": testData.csrfToken,
            },
            body: JSON.stringify({
                student_answer_id: studentAnswerId,
                answer_text: answerText,
                question_type: "sequence",
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    handleAnswerResult(questionId, submitBtn);
                } else {
                    Swal.fire({
                        title: t("errorTitle"),
                        text: data.message || t("errorOccurred"),
                        icon: "error",
                        confirmButtonText: t("ok"),
                    });
                }
            })
            .catch((error) => {
                Swal.fire({
                    title: t("errorTitle"),
                    text: t("serverError"),
                    icon: "error",
                    confirmButtonText: t("ok"),
                });
            });
    }

    function handleAnswerResult(questionId, submitBtn) {
        if (testFinished) return;

        answeredQuestions.add(questionId);

        const navBtn = document.getElementById(`navBtn${questionId}`);
        if (navBtn) {
            navBtn.classList.remove("current");
            if (!navBtn.classList.contains("answered")) {
                navBtn.classList.add("answered");
            }
            if (!navBtn.dataset.completed) {
                completedQuestions++;
                navBtn.dataset.completed = "true";
                updateProgress();
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
            questionContainer
                .querySelectorAll(".matching-select")
                .forEach((select) => (select.disabled = true));
            questionContainer
                .querySelectorAll(".sequence-input")
                .forEach((input) => (input.disabled = true));
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<i class="icofont icofont-check me-1"></i><small>${t(
                "answerSubmitted"
            )}</small>`;
        }

        setTimeout(() => {
            if (completedQuestions >= questions.length) {
                clearInterval(timerInterval);
                isTimerActive = false;
                finishTest();
            } else if (currentQuestionIndex < questions.length - 1) {
                currentQuestionIndex++;
                const nextQuestionId = questions[currentQuestionIndex].id;
                showQuestion(nextQuestionId, currentQuestionIndex + 1);
            }
        }, 1000);
    }

    function updateProgress() {
        const completedElement = document.getElementById("completedCount");
        const progressBar = document.getElementById("progressBar");

        if (completedElement) completedElement.textContent = completedQuestions;
        if (progressBar) {
            const percentage =
                questions.length > 0
                    ? (completedQuestions / questions.length) * 100
                    : 0;
            progressBar.style.width = percentage + "%";
            progressBar.setAttribute("aria-valuenow", completedQuestions);
        }
    }

    function setupFinishButton() {
        const finishBtn = document.querySelector("#finishTestBtn");
        if (finishBtn) {
            finishBtn.addEventListener("click", handleFinishClick);
        }
        return finishBtn;
    }

    function handleFinishClick(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        Swal.fire({
            title: t("finishTitle"),
            text: t("finishText"),
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#6c757d",
            confirmButtonText: t("yesFinish"),
            cancelButtonText: t("cancel"),
        }).then((result) => {
            if (result.isConfirmed) finishTest(false);
        });
    }

    function showQuestion(questionId, questionNumber) {
        if (testFinished) return;

        document
            .querySelectorAll(".question-container")
            .forEach((q) => q.classList.remove("active"));
        const targetQuestion = document.getElementById(
            `question-${questionId}`
        );
        if (targetQuestion) targetQuestion.classList.add("active");

        document
            .querySelectorAll(".nav-btn")
            .forEach((btn) => btn.classList.remove("current"));
        const navBtn = document.getElementById(`navBtn${questionId}`);
        if (navBtn && !navBtn.classList.contains("answered")) {
            navBtn.classList.add("current");
        }

        currentQuestionId = questionId;
        const currentQuestionElement =
            document.getElementById("currentQuestion");
        if (currentQuestionElement)
            currentQuestionElement.textContent = questionNumber;

        updateNavigationButtons();

        setTimeout(() => {
            if (typeof window.renderMathContent === "function") {
                window.renderMathContent();
            }
        }, 200);
    }

    function updateNavigationButtons() {
        const prevBtn = document.getElementById("prevQuestionBtn");
        const nextBtn = document.getElementById("nextQuestionBtn");

        if (prevBtn) {
            prevBtn.disabled = currentQuestionIndex === 0 || testFinished;
            prevBtn.style.display =
                currentQuestionIndex === 0 ? "none" : "inline-block";
        }

        if (nextBtn) {
            nextBtn.disabled =
                currentQuestionIndex === questions.length - 1 || testFinished;
            nextBtn.style.display =
                currentQuestionIndex === questions.length - 1
                    ? "none"
                    : "inline-block";
        }
    }

    function finishTest(isAutomatic = false) {
        if (testFinished && !isAutomatic) return;
        if (!isAutomatic && !testFinished) testFinished = true;

        clearInterval(timerInterval);
        isTimerActive = false;

        document.querySelectorAll(".nav-btn").forEach((btn) => {
            btn.disabled = true;
            btn.style.pointerEvents = "none";
        });

        if (!testData || !testData.routes || !testData.routes.finish) {
            Swal.fire({
                title: t("errorTitle"),
                text: t("finishTitle"),
                icon: "error",
                confirmButtonText: t("ok"),
            }).then(() => {
                window.location.href = "/student/home";
            });
            return;
        }

        fetch(testData.routes.finish, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": testData.csrfToken || "",
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
            body: JSON.stringify({}),
        })
            .then((response) => {
                if (!response.ok) {
                    return response.json().then((err) => {
                        throw new Error(err.message || t("serverError"));
                    });
                }
                return response.json();
            })
            .then((data) => {
                if (data.success) {
                    showResultsModal(data);
                } else {
                    Swal.fire({
                        title: t("errorTitle"),
                        text: data.message || t("errorOccurred"),
                        icon: "error",
                        confirmButtonText: t("ok"),
                    }).then(() => {
                        if (data.redirect_url)
                            window.location.href = data.redirect_url;
                    });
                }
            })
            .catch((error) => {
                Swal.fire({
                    title: t("errorTitle"),
                    text: error.message || t("serverError"),
                    icon: "error",
                    confirmButtonText: t("ok"),
                }).then(() => {
                    window.location.href = "/student/home";
                });
            });
    }

    function showResultsModal(data) {
        const totalAnswered = completedQuestions;
        const usedTime = totalSeconds - remainingTime;
        const usedMinutes = Math.floor(usedTime / 60);
        const usedSeconds = usedTime % 60;

        Swal.fire({
            title: t("testFinished"),
            html: `
                <div class="text-start">
                    <p><strong>${t(
                        "answeredQuestions"
                    )}:</strong> ${totalAnswered} / ${questions.length}</p>
                    <p><strong>${t("correctAnswers")}:</strong> ${
                data.correct_answers || 0
            }</p>
                    <p><strong>${t("score")}:</strong> ${data.score || 0}%</p>
                    <p><strong>${t(
                        "timeUsed"
                    )}:</strong> ${usedMinutes}:${usedSeconds
                .toString()
                .padStart(2, "0")}</p>
                </div>
            `,
            icon: "success",
            confirmButtonText: t("logout"),
            allowOutsideClick: false,
            allowEscapeKey: false,
        }).then(() => {
            if (testData.routes && testData.routes.logout) {
                const form = document.createElement("form");
                form.method = "POST";
                form.action = testData.routes.logout;

                const csrfInput = document.createElement("input");
                csrfInput.type = "hidden";
                csrfInput.name = "_token";
                csrfInput.value = testData.csrfToken;
                form.appendChild(csrfInput);

                document.body.appendChild(form);
                form.submit();
            } else {
                window.location.href = "/student/home";
            }
        });
    }

    window.showQuestion = showQuestion;
    window.finishTest = finishTest;

    startTimer();
    setupFinishButton();
    setupNavigationButtons();
    setupVariantCards();
    setupMatchingSelects();
    setupSequenceInputs();
    setupSubmitButtons();
    updateNavigationButtons();
    loadAnsweredQuestions();

    if (questions.length > 0) {
        showQuestion(questions[0].id, 1);
    }
});
