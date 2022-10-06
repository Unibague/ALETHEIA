import {checkIfModelHasEmptyProperties, toObjectRequest} from "@/HelperFunctions";

export default class Form {
    toObjectRequest() {
        return toObjectRequest(this);
    }

    hasEmptyProperties() {
        return checkIfModelHasEmptyProperties(this);
    }

    static fromModel(model) {
        return new Form(model.id, model.name, model.type, model.degree, model.assessment_period_id, model.unity_id, model.academic_period_id, model.unity_role, model.teaching_ladder, model.service_area_id);
    }

    constructor(id = null, name = '', type = '', degree = '', assessmentPeriodId = 0, unityId = 0, academicPeriodId = 0, unityRole = '', teachingLadder = '', serviceAreaId = '') {
        this.id = id;
        this.name = name;
        this.type = type;
        this.degree = degree;
        this.assessmentPeriodId = assessmentPeriodId;
        this.unityId = unityId;
        this.academicPeriodId = academicPeriodId;
        this.unityRole = unityRole;
        this.teachingLadder = teachingLadder;
        this.serviceAreaId = serviceAreaId;


        this.dataStructure = {
            id: null,
            name: 'required',
            type: 'required',
            degree: null,
            assessmentPeriodId: null,
            unityId: null,
            academicPeriodId: null,
            unityRole: null,
            teachingLadder: null,
            serviceAreaId: null,

        }
    }
}
