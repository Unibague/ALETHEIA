const prepareErrorText = (e) => {
    let errorsAsText = '';
    let allErrors = e.response.data.errors;
    if (allErrors !== null) {
        for (let error in allErrors) {
            allErrors[error].forEach(function (errorDescription) {
                errorsAsText += errorDescription;
            })
        }
    }
    return `${e.response.data.message} ${errorsAsText}`;
}
const checkIfModelHasEmptyProperties = (model) => {
    for (const modelKey in model) {
        if ((model[modelKey] === '' || model[modelKey] === undefined || model[modelKey] === null) && model.dataStructure[modelKey] === 'required') {
            return true;
        }
    }
    return false;
}
const clearModelProperties = (model, setNull = false) => {
    for (const modelKey in model) {
        model[modelKey] = setNull === true ? null : '';
    }
}

const showSnackbar = (snackbar, text, type = 'success', timeout = 3000) => {

    snackbar.type = type;
    snackbar.timeout = timeout;
    snackbar.status = true;
    snackbar.text = text;
}

const toObjectRequest = (model) => {
    const skipKeys = ['dataStructure'];
    let object = {};
    for (const modelKey in model) {
        if (skipKeys.includes(modelKey)) continue;

        object[camelToUnderscore(modelKey)] = model[modelKey];
    }
    return object;
}

const camelToUnderscore = (key) => {
    let result = key.replace(/([A-Z])/g, " $1");
    return result.split(' ').join('_').toLowerCase();
}

export {prepareErrorText, checkIfModelHasEmptyProperties, clearModelProperties, showSnackbar, toObjectRequest}
