export default class AssessmentPeriod {

    constructor(name = '', selfStartDate = '', selfEndDate = '', bossStartDate = '', bossEndDate = '', colleagueStartDate = '', colleagueEndDate = '', doneByNone = false, doneByAuxiliary = false, doneByAssistant = false, doneByAssociated = false, doneByHeadTeacher = false) {
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
    }
}
