import {checkIfModelHasEmptyProperties, toObjectRequest} from "@/HelperFunctions";

export default class Group {
    toObjectRequest() {
        return toObjectRequest(this);
    }

    hasEmptyProperties() {
        return checkIfModelHasEmptyProperties(this);
    }

    static fromModel(model) {
        return new Group(model.id, model.name, model.academic_period_id, model.class_code, model.group, model.degree, model.service_area_id, model.teacher_id, model.hour_type);
    }

    constructor(id = null, name = '', academicPeriodId = 0, classCode = '', group = '', degree = '', serviceAreaId  = 0, teacherId = 0, hourType = '') {
        this.id = id;
        this.name = name;
        this.academicPeriodId = academicPeriodId;
        this.classCode = classCode;
        this.group = group;
        this.degree = degree;
        this.serviceAreaId = serviceAreaId;
        this.hourType = hourType;


        this.dataStructure = {
            id: null,
            name: 'required',
            academicPeriodId: 'required',
            classCode: 'required',
            group: 'required',
            degree: 'required',
            serviceAreaId: 'required',
            teacherId: 'required',
            hourType: 'required'
        }
    }
}
