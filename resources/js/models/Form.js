import {checkIfModelHasEmptyProperties, toObjectRequest} from "@/HelperFunctions";

export default class Form {

    toObjectRequest() {
        return toObjectRequest(this);
    }

    hasEmptyProperties() {
        return checkIfModelHasEmptyProperties(this);
    }

    static fromModel(model) {
        return new Form(model.id, model.name, model.type, model.degree, model.assessment_period_id, model.unit_id, model.academic_period_id, model.unit_role, model.teaching_ladder, model.service_area_id);
    }

    getPossibleDegrees() {
        return [{name: 'pregrado'}, {name: 'posgrado'}];
    }

    getPossibleRoles() {
        return [{name: 'jefe'}, {name: 'par'}, {name: 'autoevaluación'}];
    }

    getPossibleTeachingLadders() {
        return [{name: 'ninguno'}, {name: 'auxiliar'}, {name: 'asistente'}, {name: 'asociado'}, {name: 'titular'}];
    }

    constructor(id = null, name = '', type = '', degree = null, assessmentPeriodId = null, unitId = null, academicPeriodId = null, unitRole = null, teachingLadder = null, serviceAreaId = null) {
        this.id = id;
        this.name = name;
        this.type = type;
        this.degree = degree;
        this.assessmentPeriodId = assessmentPeriodId;
        this.unitId = unitId;
        this.academicPeriodId = academicPeriodId;
        this.unitRole = unitRole;
        this.teachingLadder = teachingLadder;
        this.serviceAreaId = serviceAreaId;


        this.dataStructure = {
            id: null,
            name: 'required',
            type: null,
            degree: null,
            assessmentPeriodId: null,
            unitId: null,
            academicPeriodId: null,
            unitRole: null,
            teachingLadder: null,
            serviceAreaId: null,
        }


    }
}
