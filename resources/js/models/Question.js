import {checkIfModelHasEmptyProperties, toObjectRequest} from "@/HelperFunctions";

export default class Question {
    toObjectRequest() {
        return toObjectRequest(this);
    }

    hasEmptyProperties() {
        return checkIfModelHasEmptyProperties(this);
    }

    getPossibleCompetences() {
        return ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'];
    }

    getPossibleTypes() {
        return [
            {
                value: 'multiple',
                placeholder: 'Selección múltiple'
            },
            {
                value: 'abierta',
                placeholder: 'Pregunta abierta'
            }
        ];
    }

    static fromModel(model) {
        return new Question(model.type ?? 'multiple', model.name, model.options, model.competence);
    }

    static fromRequest(questions) {
        if (!questions) {
            return [];
        }

        let questionObjects = [];
        questions.forEach((question) => {
            questionObjects.push(Question.fromModel(question));
        });
        return questionObjects;
    }

    constructor(type, name, options = [], competence, id = null) {
        this.type = type;
        this.name = name;
        this.options = options;
        this.competence = competence;

        if (id === null) {
            this.id = Math.random().toString(16).slice(2);
        }

        this.dataStructure = {
            type: 'required',
            name: 'required',
            options: 'required',
            competence: 'required',
        };
    }
}
