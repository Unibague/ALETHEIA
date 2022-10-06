import {checkIfModelHasEmptyProperties, toObjectRequest} from "@/HelperFunctions";

export default class ResponseIdeal {
    toObjectRequest() {
        return toObjectRequest(this);
    }

    hasEmptyProperties() {
        return checkIfModelHasEmptyProperties(this);
    }

    static fromModel(model) {
        return new ResponseIdeal(model.id, model.teaching_ladder, model.form_cuestion_id, model.response);
    }

    constructor(id = null, teachingLadder = '', formCuestionId = 0, response = '' ) {
        this.id = id;
        this.teachingLadder = teachingLadder;
        this.formCuestionId = formCuestionId;
        this.response = response;

        this.dataStructure = {
            id: null,
            teachingLadder: 'required',
            formCuestionId: 'required',
            response:'required',
        }
    }
}
