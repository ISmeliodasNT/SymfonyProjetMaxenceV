import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["wrapper"]
    
    static values = {
        index: Number,
        prototype: String,
        deleteLabel: String 
    }

    add(event) {
        event.preventDefault();
        
        const prototype = this.prototypeValue;
        const newForm = prototype.replace(/__name__/g, this.indexValue);
        
        this.wrapperTarget.insertAdjacentHTML('beforeend', this.createItemHtml(newForm));
        
        this.indexValue++;
    }

    remove(event) {
        event.preventDefault();
        event.target.closest('.image-item').remove();
    }

    createItemHtml(formHtml) {
        return `
            <div class="col-md-4 image-item mb-3">
                <div class="card p-3 bg-light">
                    ${formHtml}
                    <button type="button" class="btn btn-danger btn-sm mt-2" data-action="collection#remove">
                        ${this.deleteLabelValue || 'Supprimer'} 
                    </button>
                </div>
            </div>
        `;
    }
}