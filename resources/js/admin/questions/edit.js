class QuestionsEdit {
    constructor() {
        this.languages = [];
        this.answerCount = 0;
        this.questionData = {};
        this.init();
    }

    init() {
        document.addEventListener("DOMContentLoaded", () => {
            this.loadQuestionData();
            this.loadLanguages();
            this.initializeIcons();
            this.setupImageUpload();
        });
    }

    loadQuestionData() {
        if (window.questionData) {
            this.questionData = window.questionData;
        }
    }

    initializeIcons() {
        if (typeof feather !== "undefined") {
            feather.replace();
        }
    }

    loadLanguages() {
        try {
            if (window.appLanguages && window.appLanguages.length > 0) {
                this.languages = window.appLanguages;
            } else {
                this.languages = [
                    { id: 1, name: "O'zbekcha", code: "uz" },
                    { id: 2, name: "Русский", code: "ru" },
                    { id: 3, name: "English", code: "en" },
                ];
            }
            this.renderLanguageQuestions();
        } catch (error) {
            console.error("Languages loading error:", error);
        }
    }

    renderLanguageQuestions() {
        const container = document.getElementById("languageQuestions");
        if (!container) return;

        container.innerHTML = "";
        this.answerCount = 0;

        this.languages.forEach((lang) => {
            const langDiv = document.createElement("div");
            langDiv.className = "col-12 mb-4";

            const questionText = this.questionData.questions
                ? this.questionData.questions[lang.id] || ""
                : "";

            langDiv.innerHTML = `
                <div class="language-tab">
                   <div class="language-header">
    <i class="ti ti-world me-2"></i>
    ${lang.name} (${lang.code})
</div>

                    <div class="language-content">
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i data-feather="help-circle" class="me-1"></i>
                            </label>
                            <textarea 
                                class="form-control" 
                                name="questions[${lang.id}]" 
                                placeholder="${lang.name} tilida savolni kiriting..."
                                rows="3"
                                required
                            >${questionText}</textarea>
                        </div>
                        
                        <div class="answers-section">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label fw-bold mb-0">
                                    <i class="ti ti-list me-1"></i> (${lang.name})
                                </label>
                                <button type="button" class="btn btn-success btn-sm" onclick="questionsEdit.addAnswerToLanguage(${lang.id})"><i class="icofont icofont-plus me-1"></i></button>
                            </div>
                            <div id="answers-lang-${lang.id}" class="answers-container"></div>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(langDiv);

            // Mavjud javoblarni yuklash
            this.loadExistingAnswers(lang.id);
        });

        this.initializeIcons();
    }

    loadExistingAnswers(languageId) {
        const answers = this.questionData.answers
            ? this.questionData.answers[languageId] || []
            : [];
        const correctAnswerId = this.questionData.correctAnswers
            ? this.questionData.correctAnswers[languageId]
            : null;

        if (answers.length === 0) {
            // Agar mavjud javoblar bo'lmasa, kamida 2 ta bo'sh javob qo'shish
            this.addAnswerToLanguage(languageId);
            this.addAnswerToLanguage(languageId);
        } else {
            // Mavjud javoblarni yuklash
            answers.forEach((answer) => {
                this.addExistingAnswerToLanguage(
                    languageId,
                    answer,
                    correctAnswerId == answer.id
                );
            });
        }
    }

    addExistingAnswerToLanguage(languageId, answerData, isCorrect = false) {
        this.answerCount++;
        const container = document.getElementById(`answers-lang-${languageId}`);
        if (!container) return;

        const globalAnswerId = this.answerCount;

        const answerDiv = document.createElement("div");
        answerDiv.className = "answer-item";
        if (isCorrect) {
            answerDiv.classList.add("correct-answer");
        }
        answerDiv.id = `answer-${languageId}-${globalAnswerId}`;

        answerDiv.innerHTML = `
            <div class="d-flex align-items-center mb-2">
                <div class="form-check me-3">
                    <input 
                        class="form-check-input" 
                        type="radio" 
                        name="correct_answer_${languageId}" 
                        id="correct_${languageId}_${globalAnswerId}"
                        value="${answerData.id || globalAnswerId}"
                        ${isCorrect ? "checked" : ""}
                        onchange="questionsEdit.markCorrectAnswerForLanguage(${languageId}, ${globalAnswerId})"
                    >
                    <label class="form-check-label text-success" for="correct_${languageId}_${globalAnswerId}">
                        <i data-feather="check" class="me-1"></i>
                    </label>
                </div>
                <div class="flex-grow-1">
                    <input 
                        type="text" 
                        class="form-control" 
                        name="answers[${languageId}][${
            answerData.id || globalAnswerId
        }][text]"
                        placeholder="Javob..."
                        value="${answerData.text || ""}"
                        required
                    >
                    <input type="hidden" name="answers[${languageId}][${
            answerData.id || globalAnswerId
        }][answer_id]" value="${answerData.id || globalAnswerId}">
                    ${
                        answerData.id
                            ? `<input type="hidden" name="answers[${languageId}][${answerData.id}][existing_id]" value="${answerData.id}">`
                            : ""
                    }
                </div>
                <button type="button" class="btn btn-danger btn-sm ms-2" onclick="questionsEdit.removeAnswerFromLanguage(${languageId}, ${globalAnswerId})">
                    <i data-feather="trash-2"></i>
                </button>
            </div>
        `;

        container.appendChild(answerDiv);
        this.initializeIcons();
    }

    addAnswerToLanguage(languageId) {
        this.answerCount++;
        const container = document.getElementById(`answers-lang-${languageId}`);
        if (!container) return;

        const currentAnswers = container.children.length + 1;
        const globalAnswerId = this.answerCount;

        const answerDiv = document.createElement("div");
        answerDiv.className = "answer-item";
        answerDiv.id = `answer-${languageId}-${globalAnswerId}`;

        answerDiv.innerHTML = `
            <div class="d-flex align-items-center mb-2">
                <div class="form-check me-3">
                    <input 
                        class="form-check-input" 
                        type="radio" 
                        name="correct_answer_${languageId}" 
                        id="correct_${languageId}_${globalAnswerId}"
                        value="${globalAnswerId}"
                        onchange="questionsEdit.markCorrectAnswerForLanguage(${languageId}, ${globalAnswerId})"
                    >
                    <label class="form-check-label text-success" for="correct_${languageId}_${globalAnswerId}">
                        <i data-feather="check" class="me-1"></i>To'g'ri
                    </label>
                </div>
                <div class="flex-grow-1">
                    <input 
                        type="text" 
                        class="form-control" 
                        name="answers[${languageId}][${globalAnswerId}][text]"
                        placeholder="Javob ${currentAnswers}..."
                        required
                    >
                    <input type="hidden" name="answers[${languageId}][${globalAnswerId}][answer_id]" value="${globalAnswerId}">
                </div>
                <button type="button" class="btn btn-danger btn-sm ms-2" onclick="questionsEdit.removeAnswerFromLanguage(${languageId}, ${globalAnswerId})">
                    <i data-feather="trash-2"></i>
                </button>
            </div>
        `;

        container.appendChild(answerDiv);
        this.initializeIcons();
    }

    removeAnswerFromLanguage(languageId, answerId) {
        const element = document.getElementById(
            `answer-${languageId}-${answerId}`
        );
        const container = document.getElementById(`answers-lang-${languageId}`);

        if (element && container && container.children.length > 1) {
            element.remove();
        } else {
            alert("Kamida bitta javob bo'lishi shart!");
        }
    }

    markCorrectAnswerForLanguage(languageId, answerId) {
        const answers = document.querySelectorAll(
            `#answers-lang-${languageId} .answer-item`
        );
        answers.forEach((answer) => {
            answer.classList.remove("correct-answer");
        });

        const selectedAnswer = document.getElementById(
            `answer-${languageId}-${answerId}`
        );
        if (selectedAnswer) {
            selectedAnswer.classList.add("correct-answer");
        }
    }

    setupImageUpload() {
        const imageInput = document.getElementById("imageInput");
        const imagePreview = document.getElementById("imagePreview");
        const previewImg = document.getElementById("previewImg");
        const fileName = document.getElementById("fileName");
        const fileSize = document.getElementById("fileSize");
        const container = document.querySelector(".image-upload-container");
        const currentImage = document.getElementById("currentImage");
        const removeCurrentImageInput =
            document.getElementById("removeCurrentImage");
        const removePreviewBtn = document.querySelector(
            "#imagePreview .btn-remove"
        );
        const removeCurrentBtn = document.querySelector(
            "#currentImage .btn-remove"
        );

        if (!imageInput) return;

        imageInput.addEventListener("change", (e) => {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (e) => {
                previewImg.src = e.target.result;
                fileName.textContent = file.name;
                fileSize.textContent = (file.size / 1024).toFixed(1) + " KB";
                imagePreview.style.display = "block";
                container.classList.add("has-image");

                if (currentImage) currentImage.style.display = "none";
            };
            reader.readAsDataURL(file);
        });

        if (removePreviewBtn) {
            removePreviewBtn.addEventListener("click", () => {
                imageInput.value = "";
                imagePreview.style.display = "none";
                if (currentImage) currentImage.style.display = "block";
                else container.classList.remove("has-image");
            });
        }

        if (removeCurrentBtn) {
            removeCurrentBtn.addEventListener("click", () => {
                if (currentImage) currentImage.style.display = "none";
                removeCurrentImageInput.value = "1";
                container.classList.remove("has-image");

                const uploadBtn = document.querySelector(".btn-upload");
                if (uploadBtn) {
                    uploadBtn.innerHTML = `<i class="icofont icofont-upload me-2"></i>${translations[lang]["Select Image"]}`;
                }
            });
        }
    }

    addAnswerToAll() {
        this.languages.forEach((lang) => {
            this.addAnswerToLanguage(lang.id);
        });
    }
}

const questionsEdit = new QuestionsEdit();
window.questionsEdit = questionsEdit;
