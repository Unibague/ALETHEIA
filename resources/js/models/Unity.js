import {checkIfModelHasEmptyProperties, toObjectRequest} from "@/HelperFunctions";

export default class Unity {
    toObjectRequest() {
        return toObjectRequest(this);
    }

    hasEmptyProperties() {
        return checkIfModelHasEmptyProperties(this);
    }

    static fromModel(model) {
        return new Unity(model.id, model.name, model.code, model.is_custom, model.assessment_period_id);
    }

    constructor(id = null, name = '', code = '', isCustom = '', assessmentPeriodId = 0) {
        this.id = id;
        this.name = name;
        this.code = code;
        this.isCustom = isCustom;
        this.assessmentPeriodId = assessmentPeriodId;

        this.dataStructure = {
            id: null,
            name: 'required',
            code: null,
            isCustom: null,
            assessmentPeriodId: null,
        }
    }
}
