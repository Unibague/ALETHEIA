import {checkIfModelHasEmptyProperties, toObjectRequest} from "@/HelperFunctions";
import Question from "@/models/Question";

export default class FormQuestions {
    toObjectRequest() {
        return toObjectRequest(this);
    }

    hasEmptyProperties() {
        return checkIfModelHasEmptyProperties(this);
    }


    static fromModel(model) {
        return new FormQuestions(model.id, model.form_id, JSON.parse(model.questions));
    }

    constructor(id = null, formId = 0, questions = [new Question()]) {
        this.id = id;
        this.formId = formId;
        this.questions = Question.fromModel(questions);

        this.dataStructure = {
            id: null,
            formId: 'required',
            questions: 'required',
        }
    }
}
