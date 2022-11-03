import {checkIfModelHasEmptyProperties, toObjectRequest} from "@/HelperFunctions";

export default class Form {

    toObjectRequest() {
        return toObjectRequest(this);
    }

    hasEmptyProperties() {
        return checkIfModelHasEmptyProperties(this);
    }

    static fromModel(model) {
        console.log('crear modelo', model);
        return new Form(model.id, model.name, model.type, model.degree, model.assessment_period_id, JSON.parse(model.units_id), model.academic_period_id, model.unit_role, model.teaching_ladder, model.service_areas_id);
    }

    static getPossibleDegrees() {
        return [
            {name: 'Todos', value: null},
            {name: 'pregrado', value: 'pregrado'},
            {name: 'posgrado', value: 'posgrado'},
            {name: 'cursos', value: 'cursos'},
        ];
    }

    static getPossibleRoles() {
        return [
            {name: 'Todos', value: null},
            {name: 'jefe', value: 'jefe'},
            {name: 'par', value: 'par'},
            {
                name: 'autoevaluación',
                value: 'autoevaluación'
            }
        ];
    }

    static getPossibleTeachingLadders() {
        return [
            {name: 'Todos', value: null},
            {name: 'ninguno', value: 'ninguno'},
            {name: 'auxiliar', value: 'auxiliar'},
            {
                name: 'asistente', value: 'asistente'
            },
            {name: 'asociado', value: 'asociado'},
            {name: 'titular', value: 'titular'}
        ];
    }

    constructor(id = null, name = '', type = '', degree = null, assessmentPeriodId = null, unitsId = null, academicPeriodId = null, unitRole = null, teachingLadder = null, serviceAreasId = null) {
        this.id = id;
        this.name = name;
        this.type = type;
        this.degree = degree;
        this.assessmentPeriodId = assessmentPeriodId;
        this.unitsId = unitsId === null ? [{id: null, name: 'Todas'}] : unitsId;
        this.academicPeriodId = academicPeriodId;
        this.unitRole = unitRole;
        this.teachingLadder = teachingLadder;
        this.serviceAreasId = serviceAreasId === null ? [{id: null, name: 'Todas'}] : serviceAreasId;


        this.dataStructure = {
            id: null,
            name: 'required',
            type: null,
            degree: null,
            assessmentPeriodId: null,
            unitsId: null,
            academicPeriodId: null,
            unitRole: null,
            teachingLadder: null,
            serviceAreasId: null,
        }


    }
}
