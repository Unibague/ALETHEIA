import {checkIfModelHasEmptyProperties, toObjectRequest} from "@/HelperFunctions";

export default class AssessmentPeriod {
    toObjectRequest() {
        return toObjectRequest(this);
    }

    hasEmptyProperties() {
        return checkIfModelHasEmptyProperties(this);
    }

    static fromModel(model) {
        return new AssessmentPeriod(model.id, model.name, model.self_start_date, model.self_end_date, model.boss_start_date, model.boss_end_date, model.colleague_start_date, model.colleague_end_date, model.done_by_none, model.done_by_auxiliary, model.done_by_assistant, model.done_by_associated, model.done_by_head_teacher);
    }

    constructor(id = null, name = '', selfStartDate = '', selfEndDate = '', bossStartDate = '', bossEndDate = '', colleagueStartDate = '', colleagueEndDate = '', doneByNone = false, doneByAuxiliary = false, doneByAssistant = false, doneByAssociated = false, doneByHeadTeacher = false) {
        this.id = id;
        this.name = name;
        this.selfStartDate = selfStartDate;
        this.selfEndDate = selfEndDate;
        this.bossStartDate = bossStartDate;
        this.bossEndDate = bossEndDate;
        this.colleagueStartDate = colleagueStartDate;
        this.colleagueEndDate = colleagueEndDate;
        this.doneByNone = doneByNone;
        this.doneByAuxiliary = doneByAuxiliary;
        this.doneByAssistant = doneByAssistant;
        this.doneByAssociated = doneByAssociated;
        this.doneByHeadTeacher = doneByHeadTeacher;

        this.dataStructure = {
            id: null,
            name: 'required',
            selfStartDate: 'required',
            selfEndDate: 'required',
            bossStartDate: 'required',
            bossEndDate: 'required',
            colleagueStartDate: 'required',
            colleagueEndDate: 'required',
            doneByNone: 'required',
            doneByAuxiliary: 'required',
            doneByAssociated: 'required',
            doneByHeadTeacher: 'required',
        }
    }
}
