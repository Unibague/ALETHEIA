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


    getLineChartColors(){

        return [{role: 'autoevaluación', color: 'blue'}, {role: 'jefe', color: 'red'}, {role: 'par', color: 'green'}, {role: 'estudiante', color: 'purple'}]

    }


    getPossibleCompetencesAsArrayOfObjects() {

        let finalCompetences = []
        let competences = ['C1', 'C2', 'C3', 'C4', 'C5', 'C6']

        competences.forEach(competence =>{


            if(competence == 'C1'){

                finalCompetences.push({id: 0, competence, name: '', value:''})
            }

            if(competence == 'C2'){

                finalCompetences.push({id: 1, competence, name: '', value:''})
            }
            if(competence == 'C3'){

                finalCompetences.push({id: 2, competence, name: '', value:''})
            }
            if(competence == 'C4'){

                finalCompetences.push({id: 3, competence, name: '', value:''})
            }
            if(competence == 'C5'){

                finalCompetences.push({id: 4, competence, name: '', value:''})
            }
            if(competence == 'C6'){

                finalCompetences.push({id: 5, competence, name: '', value:''})
            }



        })

        return finalCompetences;
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
