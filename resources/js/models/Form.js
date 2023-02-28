import {checkIfModelHasEmptyProperties, toObjectRequest} from "@/HelperFunctions";

export default class Form {

    toObjectRequest() {
        return toObjectRequest(this);
    }

    hasEmptyProperties() {
        return checkIfModelHasEmptyProperties(this);
    }

    static copy(form) {
        return new Form(form.id, form.name, form.description, form.type, form.degree, form.assessmentPeriod, form.units, form.academicPeriod, form.unitRole, form.teachingLadder, form.serviceAreas);
    }

    static createFormsFromArray(models) {
        let forms = []
        models.forEach(function (model) {
            forms.push(Form.fromModel(model));
        })
        return forms;
    }

    static fromModel(model) {
        return new Form(model.id, model.name, model.description, model.type, model.degree, model.assessment_period, JSON.parse(model.units), model.academic_period ?? {
            id: null,
            name: 'Todos'
        }, model.unit_role, model.teaching_ladder, JSON.parse(model.service_areas));
    }

    static getPossibleDegrees() {
        return [
            {name: 'Todos', value: null},
            {name: 'pregrado', value: 'pregrado'},
            {name: 'posgrado', value: 'posgrado'},
            {name: 'Cursos', value: 'cursos'},
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

    constructor(id = null, name = '', description='',type = '', degree = null, assessmentPeriod = null, units = null, academicPeriod = {
        id: null,
        name: 'Todos'
    }, unitRole = null, teachingLadder = null, serviceAreas = null) {
        this.id = id;
        this.name = name;
        this.description = description;
        this.type = type;
        this.degree = degree;
        this.assessmentPeriod = assessmentPeriod ?? {id: null, name: 'Todos'};
        this.units = units === null ? [{id: null, name: 'Todas'}] : units;
        this.academicPeriod = academicPeriod;
        this.unitRole = unitRole;
        this.teachingLadder = teachingLadder;
        this.serviceAreas = serviceAreas === null ? [{id: null, name: 'Todas'}] : serviceAreas;


        this.dataStructure = {
            id: null,
            name: 'required',
            description: null,
            type: null,
            degree: null,
            assessmentPeriod: null,
            units: null,
            academicPeriod: null,
            unitRole: null,
            teachingLadder: null,
            serviceAreas: null,
        }


    }
}
