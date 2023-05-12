import {checkIfModelHasEmptyProperties, toObjectRequest} from "@/HelperFunctions";

export default class Unit {
    toObjectRequest() {
        return toObjectRequest(this);
    }

    hasEmptyProperties() {
        return checkIfModelHasEmptyProperties(this);
    }

    static fromModel(model) {
        return new Unit(model.id, model.name, model.code, model.is_custom, model.identifier, model.assessment_period_id);
    }

    constructor(id = null, name = '', code = '', isCustom = '', identifier = '', assessmentPeriodId = 0) {
        this.id = id;
        this.name = name;
        this.code = code;
        this.identifier = code+"-"+assessmentPeriodId
        this.isCustom = isCustom;
        this.assessmentPeriodId = assessmentPeriodId;

        this.dataStructure = {
            id: null,
            name: 'required',
            code: null,
            identifier: null,
            isCustom: null,
            assessmentPeriodId: null,
        }
    }
}
