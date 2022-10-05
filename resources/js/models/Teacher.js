import {checkIfModelHasEmptyProperties, toObjectRequest} from "@/HelperFunctions";

export default class Teacher {
    toObjectRequest() {
        return toObjectRequest(this);
    }

    hasEmptyProperties() {
        return checkIfModelHasEmptyProperties(this);
    }

    static fromModel(model) {
        return new Teacher(model.id, model.assessment_period_id, model.identification_number, model.user_id, model.unity, model.position, model.teaching_ladder, model.employee_type, model.status);
    }

    constructor(id = null, assessmentPeriodId = 0, identificationNumber = '', userId = 0, unity = '', position = '', teachingLadder = '', employeeType = '', status = 'active') {
        this.id = id;
        this.assessmentPeriodId = assessmentPeriodId;
        this.identificationNumber = identificationNumber;
        this.userId = userId;
        this.unity = unity;
        this.position = position;
        this.teachingLadder = teachingLadder;
        this.employeeType = employeeType;
        this.status = status;

        this.dataStructure = {
            id: null,
            assessmentPeriodId: 'required',
            identification_number: 'required',
            userId: 'required',
            unity: 'required',
            position: 'required',
            teachingLadder: 'required',
            employeeType: 'required',
            status: 'required',
        }
    }
}
