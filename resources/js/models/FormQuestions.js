import {checkIfModelHasEmptyProperties, toObjectRequest} from "@/HelperFunctions";

export default class FormQuestions {
    toObjectRequest() {
        return toObjectRequest(this);
    }

    hasEmptyProperties() {
        return checkIfModelHasEmptyProperties(this);
    }

    getPossibleCompetences() {
        return ['C1', 'C2', 'C3', 'C4', 'C5'];
    }

    static fromModel(model) {
        return new FormQuestions(model.id, model.form_id, model.questions, model.answer_options);
    }

    constructor(id = null, formId = 0, questions = [{options:[{}]}], answerOptions = '') {
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
