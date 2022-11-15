import {checkIfModelHasEmptyProperties, toObjectRequest} from "@/HelperFunctions";

export default class AcademicPeriod {
    toObjectRequest() {
        return toObjectRequest(this, true);
    }

    hasEmptyProperties() {
        return checkIfModelHasEmptyProperties(this);
    }

    static fromModel(model) {
        return new AcademicPeriod(model.id, model.name, model.class_start_date, model.class_end_date, model.students_start_date, model.students_end_date, model.assessment_period_id);
    }

    constructor(id = null, name = '', classStartDate = '', classEndDate = '', studentsStartDate = '', studentsEndDate = '', assessmentPeriodId = 0) {
        this.id = id;
        this.name = name;
        this.classStartDate = classStartDate;
        this.classEndDate = classEndDate;
        this.studentsStartDate = studentsStartDate;
        this.studentsEndDate = studentsEndDate;
        this.assessmentPeriodId = assessmentPeriodId;

        this.dataStructure = {
            id: null,
            name: 'required',
            classStartDate: 'required',
            classEndDate: 'required',
            studentsStartDate: 'required',
            studentsEndDate: 'required',
            assessmentPeriodId: null,
        }
    }
}
