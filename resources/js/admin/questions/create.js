class QuestionsCreate {
    constructor() {
        this.languages = [];
        this.answerCount = 0;
        this.init();
    }

    init() {
        document.addEventListener("DOMContentLoaded", () => {
            this.loadLanguages();
            this.initializeIcons();
            this.setupImageUpload();
        });
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

        this.languages.forEach((lang) => {
            const langDiv = document.createElement("div");
            langDiv.className = "col-12 mb-4";
            langDiv.innerHTML = `
                <div class="language-tab">
                    <div class="language-header">
                        <i class="ti ti-world me-2"></i>${
                            lang.name
                        } (${lang.code.toUpperCase()})
                    </div>
                    <div class="language-content">
                        <div class="mb-3">
                            <textarea 
                                class="form-control" 
                                name="questions[${lang.id}]" 
                                placeholder="${window.translations[
                                    "Enter question in :lang..."
                                ].replace(":lang", lang.name)}"
                                rows="3"
                                required
                            ></textarea>
                        </div>
                        
                        <div class="answers-section">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label fw-bold mb-0">
                                    <i class="ti ti-list me-1"></i>${window.translations[
                                        "Answer variants (:lang)"
                                    ].replace(":lang", lang.name)}
                                </label>
                                <button type="button" class="btn btn-success btn-sm" onclick="questionsCreate.addAnswerToLanguage(${
                                    lang.id
                                })">
                                    <i class="icofont icofont-plus me-1"></i>${
                                        window.translations["Add Answer"]
                                    }
                                </button>
                            </div>
                            <div id="answers-lang-${
                                lang.id
                            }" class="answers-container"></div>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(langDiv);

            this.addAnswerToLanguage(lang.id);
            this.addAnswerToLanguage(lang.id);
        });

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
                        onchange="questionsCreate.markCorrectAnswerForLanguage(${languageId}, ${globalAnswerId})"
                    >
                </div>
                <div class="flex-grow-1">
                    <input 
                        type="text" 
                        class="form-control" 
                        name="answers[${languageId}][${globalAnswerId}][text]"
                        placeholder="${window.translations["Answer"]} ${currentAnswers}..."
                        required
                    >
                    <input type="hidden" name="answers[${languageId}][${globalAnswerId}][answer_id]" value="${globalAnswerId}">
                </div>
                <button type="button" class="btn btn-danger btn-sm ms-2" onclick="questionsCreate.removeAnswerFromLanguage(${languageId}, ${globalAnswerId})">
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
            Swal.fire({
                icon: "warning",
                title: window.translations["At least one answer is required!"],
                confirmButtonText: "OK",
            });
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

        if (!imageInput) return;

        imageInput.addEventListener("change", function (e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (e) {
                previewImg.src = e.target.result;
                fileName.textContent = file.name;
                fileSize.textContent = (file.size / 1024).toFixed(1) + " KB";
                imagePreview.style.display = "block";
                container.classList.add("has-image");
            };
            reader.readAsDataURL(file);
        });

        window.removeImagePreview = function () {
            imageInput.value = "";
            imagePreview.style.display = "none";
            container.classList.remove("has-image");
        };
    }

    addAnswerToAll() {
        this.languages.forEach((lang) => {
            this.addAnswerToLanguage(lang.id);
        });
    }
}

const questionsCreate = new QuestionsCreate();
window.questionsCreate = questionsCreate;
