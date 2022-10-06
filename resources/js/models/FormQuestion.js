import {checkIfModelHasEmptyProperties, toObjectRequest} from "@/HelperFunctions";

export default class FormQuestion {
    toObjectRequest() {
        return toObjectRequest(this);
    }

    hasEmptyProperties() {
        return checkIfModelHasEmptyProperties(this);
    }

    static fromModel(model) {
        return new FormQuestion(model.id, model.form_id, model.questions, model.answer_options);
    }

    constructor(id = null, formId = 0, questions = '', answerOptions = '' ) {
        this.id = id;
        this.formId = formId;
        this.questions = questions;
        this.answerOptions = answerOptions;

        this.dataStructure = {
            id: null,
            formId: 'required',
            questions: 'required',
            answerOptions: 'required',
        }
    }
}
