class l{constructor(){this.languages=[],this.answerCount=0,this.init()}init(){document.addEventListener("DOMContentLoaded",()=>{this.loadLanguages(),this.initializeIcons(),this.setupImageUpload()})}initializeIcons(){typeof feather<"u"&&feather.replace()}loadLanguages(){try{window.appLanguages&&window.appLanguages.length>0?this.languages=window.appLanguages:this.languages=[{id:1,name:"O'zbekcha",code:"uz"},{id:2,name:"Русский",code:"ru"},{id:3,name:"English",code:"en"}],this.renderLanguageQuestions()}catch(e){console.error("Languages loading error:",e)}}renderLanguageQuestions(){const e=document.getElementById("languageQuestions");e&&(e.innerHTML="",this.languages.forEach(n=>{const s=document.createElement("div");s.className="col-12 mb-4",s.innerHTML=`
                <div class="language-tab">
                    <div class="language-header">
                        <i class="ti ti-world me-2"></i>${n.name} (${n.code.toUpperCase()})
                    </div>
                    <div class="language-content">
                        <div class="mb-3">
                            <textarea 
                                class="form-control" 
                                name="questions[${n.id}]" 
                                placeholder="${window.translations["Enter question in :lang..."].replace(":lang",n.name)}"
                                rows="3"
                                required
                            ></textarea>
                        </div>
                        
                        <div class="answers-section">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label fw-bold mb-0">
                                    <i class="ti ti-list me-1"></i>${window.translations["Answer variants (:lang)"].replace(":lang",n.name)}
                                </label>
                                <button type="button" class="btn btn-success btn-sm" onclick="questionsCreate.addAnswerToLanguage(${n.id})">
                                    <i class="icofont icofont-plus me-1"></i>${window.translations["Add Answer"]}
                                </button>
                            </div>
                            <div id="answers-lang-${n.id}" class="answers-container"></div>
                        </div>
                    </div>
                </div>
            `,e.appendChild(s),this.addAnswerToLanguage(n.id),this.addAnswerToLanguage(n.id)}),this.initializeIcons())}addAnswerToLanguage(e){this.answerCount++;const n=document.getElementById(`answers-lang-${e}`);if(!n)return;const s=n.children.length+1,t=this.answerCount,a=document.createElement("div");a.className="answer-item",a.id=`answer-${e}-${t}`,a.innerHTML=`
            <div class="d-flex align-items-center mb-2">
                <div class="form-check me-3">
                    <input 
                        class="form-check-input" 
                        type="radio" 
                        name="correct_answer_${e}" 
                        id="correct_${e}_${t}"
                        value="${t}"
                        onchange="questionsCreate.markCorrectAnswerForLanguage(${e}, ${t})"
                    >
                </div>
                <div class="flex-grow-1">
                    <input 
                        type="text" 
                        class="form-control" 
                        name="answers[${e}][${t}][text]"
                        placeholder="${window.translations.Answer} ${s}..."
                        required
                    >
                    <input type="hidden" name="answers[${e}][${t}][answer_id]" value="${t}">
                </div>
                <button type="button" class="btn btn-danger btn-sm ms-2" onclick="questionsCreate.removeAnswerFromLanguage(${e}, ${t})">
                    <i data-feather="trash-2"></i>
                </button>
            </div>
        `,n.appendChild(a),this.initializeIcons()}removeAnswerFromLanguage(e,n){const s=document.getElementById(`answer-${e}-${n}`),t=document.getElementById(`answers-lang-${e}`);s&&t&&t.children.length>1?s.remove():Swal.fire({icon:"warning",title:window.translations["At least one answer is required!"],confirmButtonText:"OK"})}markCorrectAnswerForLanguage(e,n){document.querySelectorAll(`#answers-lang-${e} .answer-item`).forEach(a=>{a.classList.remove("correct-answer")});const t=document.getElementById(`answer-${e}-${n}`);t&&t.classList.add("correct-answer")}setupImageUpload(){const e=document.getElementById("imageInput"),n=document.getElementById("imagePreview"),s=document.getElementById("previewImg"),t=document.getElementById("fileName"),a=document.getElementById("fileSize"),r=document.querySelector(".image-upload-container");e&&(e.addEventListener("change",function(c){const i=c.target.files[0];if(!i)return;const o=new FileReader;o.onload=function(d){s.src=d.target.result,t.textContent=i.name,a.textContent=(i.size/1024).toFixed(1)+" KB",n.style.display="block",r.classList.add("has-image")},o.readAsDataURL(i)}),window.removeImagePreview=function(){e.value="",n.style.display="none",r.classList.remove("has-image")})}addAnswerToAll(){this.languages.forEach(e=>{this.addAnswerToLanguage(e.id)})}}const m=new l;window.questionsCreate=m;
