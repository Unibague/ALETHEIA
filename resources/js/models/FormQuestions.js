import {checkIfModelHasEmptyProperties, toObjectRequest} from "@/HelperFunctions";

export default class FormQuestions {
    toObjectRequest() {
        return toObjectRequest(this);
    }

    hasEmptyProperties() {
        return checkIfModelHasEmptyProperties(this);
    }

    getPossibleCompetences() {
        return ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'];
    }

    static fromModel(model) {
        return new FormQuestions(model.id, model.form_id, JSON.parse(model.questions));
    }

    constructor(id = null, formId = 0, questions = [{options: [{}]}]) {
        this.id = id;
        this.formId = formId;
        this.questions = questions;

        this.dataStructure = {
            id: null,
            formId: 'required',
            questions: 'required',
        }
    }
}
