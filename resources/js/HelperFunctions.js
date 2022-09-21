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
        if (model[modelKey] === '' || model[modelKey] === undefined) {
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
export {prepareErrorText, checkIfModelHasEmptyProperties, clearModelProperties}
