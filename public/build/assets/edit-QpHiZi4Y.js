class w{constructor(){this.languages=[],this.answerCount=0,this.questionData={},this.init()}init(){document.addEventListener("DOMContentLoaded",()=>{this.loadQuestionData(),this.loadLanguages(),this.initializeIcons(),this.setupImageUpload()})}loadQuestionData(){window.questionData&&(this.questionData=window.questionData)}initializeIcons(){typeof feather<"u"&&feather.replace()}loadLanguages(){try{window.appLanguages&&window.appLanguages.length>0?this.languages=window.appLanguages:this.languages=[{id:1,name:"O'zbekcha",code:"uz"},{id:2,name:"Русский",code:"ru"},{id:3,name:"English",code:"en"}],this.renderLanguageQuestions()}catch(e){console.error("Languages loading error:",e)}}renderLanguageQuestions(){const e=document.getElementById("languageQuestions");e&&(e.innerHTML="",this.answerCount=0,this.languages.forEach(t=>{const i=document.createElement("div");i.className="col-12 mb-4";const s=this.questionData.questions&&this.questionData.questions[t.id]||"";i.innerHTML=`
                <div class="language-tab">
                   <div class="language-header">
    <i class="ti ti-world me-2"></i>
    ${t.name} (${t.code})
</div>

                    <div class="language-content">
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i data-feather="help-circle" class="me-1"></i>
                            </label>
                            <textarea 
                                class="form-control" 
                                name="questions[${t.id}]" 
                                placeholder="${t.name} tilida savolni kiriting..."
                                rows="3"
                                required
                            >${s}</textarea>
                        </div>
                        
                        <div class="answers-section">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label fw-bold mb-0">
                                    <i class="ti ti-list me-1"></i> (${t.name})
                                </label>
                                <button type="button" class="btn btn-success btn-sm" onclick="questionsEdit.addAnswerToLanguage(${t.id})"><i class="icofont icofont-plus me-1"></i></button>
                            </div>
                            <div id="answers-lang-${t.id}" class="answers-container"></div>
                        </div>
                    </div>
                </div>
            `,e.appendChild(i),this.loadExistingAnswers(t.id)}),this.initializeIcons())}loadExistingAnswers(e){const t=this.questionData.answers?this.questionData.answers[e]||[]:[],i=this.questionData.correctAnswers?this.questionData.correctAnswers[e]:null;t.length===0?(this.addAnswerToLanguage(e),this.addAnswerToLanguage(e)):t.forEach(s=>{this.addExistingAnswerToLanguage(e,s,i==s.id)})}addExistingAnswerToLanguage(e,t,i=!1){this.answerCount++;const s=document.getElementById(`answers-lang-${e}`);if(!s)return;const n=this.answerCount,a=document.createElement("div");a.className="answer-item",i&&a.classList.add("correct-answer"),a.id=`answer-${e}-${n}`,a.innerHTML=`
            <div class="d-flex align-items-center mb-2">
                <div class="form-check me-3">
                    <input 
                        class="form-check-input" 
                        type="radio" 
                        name="correct_answer_${e}" 
                        id="correct_${e}_${n}"
                        value="${t.id||n}"
                        ${i?"checked":""}
                        onchange="questionsEdit.markCorrectAnswerForLanguage(${e}, ${n})"
                    >
                    <label class="form-check-label text-success" for="correct_${e}_${n}">
                        <i data-feather="check" class="me-1"></i>
                    </label>
                </div>
                <div class="flex-grow-1">
                    <input 
                        type="text" 
                        class="form-control" 
                        name="answers[${e}][${t.id||n}][text]"
                        placeholder="Javob..."
                        value="${t.text||""}"
                        required
                    >
                    <input type="hidden" name="answers[${e}][${t.id||n}][answer_id]" value="${t.id||n}">
                    ${t.id?`<input type="hidden" name="answers[${e}][${t.id}][existing_id]" value="${t.id}">`:""}
                </div>
                <button type="button" class="btn btn-danger btn-sm ms-2" onclick="questionsEdit.removeAnswerFromLanguage(${e}, ${n})">
                    <i data-feather="trash-2"></i>
                </button>
            </div>
        `,s.appendChild(a),this.initializeIcons()}addAnswerToLanguage(e){this.answerCount++;const t=document.getElementById(`answers-lang-${e}`);if(!t)return;const i=t.children.length+1,s=this.answerCount,n=document.createElement("div");n.className="answer-item",n.id=`answer-${e}-${s}`,n.innerHTML=`
            <div class="d-flex align-items-center mb-2">
                <div class="form-check me-3">
                    <input 
                        class="form-check-input" 
                        type="radio" 
                        name="correct_answer_${e}" 
                        id="correct_${e}_${s}"
                        value="${s}"
                        onchange="questionsEdit.markCorrectAnswerForLanguage(${e}, ${s})"
                    >
                    <label class="form-check-label text-success" for="correct_${e}_${s}">
                        <i data-feather="check" class="me-1"></i>To'g'ri
                    </label>
                </div>
                <div class="flex-grow-1">
                    <input 
                        type="text" 
                        class="form-control" 
                        name="answers[${e}][${s}][text]"
                        placeholder="Javob ${i}..."
                        required
                    >
                    <input type="hidden" name="answers[${e}][${s}][answer_id]" value="${s}">
                </div>
                <button type="button" class="btn btn-danger btn-sm ms-2" onclick="questionsEdit.removeAnswerFromLanguage(${e}, ${s})">
                    <i data-feather="trash-2"></i>
                </button>
            </div>
        `,t.appendChild(n),this.initializeIcons()}removeAnswerFromLanguage(e,t){const i=document.getElementById(`answer-${e}-${t}`),s=document.getElementById(`answers-lang-${e}`);i&&s&&s.children.length>1?i.remove():alert("Kamida bitta javob bo'lishi shart!")}markCorrectAnswerForLanguage(e,t){document.querySelectorAll(`#answers-lang-${e} .answer-item`).forEach(n=>{n.classList.remove("correct-answer")});const s=document.getElementById(`answer-${e}-${t}`);s&&s.classList.add("correct-answer")}setupImageUpload(){const e=document.getElementById("imageInput"),t=document.getElementById("imagePreview"),i=document.getElementById("previewImg"),s=document.getElementById("fileName"),n=document.getElementById("fileSize"),a=document.querySelector(".image-upload-container"),o=document.getElementById("currentImage"),u=document.getElementById("removeCurrentImage"),d=document.querySelector("#imagePreview .btn-remove"),l=document.querySelector("#currentImage .btn-remove");e&&(e.addEventListener("change",r=>{const c=r.target.files[0];if(!c)return;const m=new FileReader;m.onload=h=>{i.src=h.target.result,s.textContent=c.name,n.textContent=(c.size/1024).toFixed(1)+" KB",t.style.display="block",a.classList.add("has-image"),o&&(o.style.display="none")},m.readAsDataURL(c)}),d&&d.addEventListener("click",()=>{e.value="",t.style.display="none",o?o.style.display="block":a.classList.remove("has-image")}),l&&l.addEventListener("click",()=>{o&&(o.style.display="none"),u.value="1",a.classList.remove("has-image");const r=document.querySelector(".btn-upload");r&&(r.innerHTML=`<i class="icofont icofont-upload me-2"></i>${translations[lang]["Select Image"]}`)}))}addAnswerToAll(){this.languages.forEach(e=>{this.addAnswerToLanguage(e.id)})}}const g=new w;window.questionsEdit=g;
