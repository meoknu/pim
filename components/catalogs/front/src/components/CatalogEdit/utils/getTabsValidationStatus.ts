import {CatalogFormErrors} from '../models/CatalogFormErrors';
import {Tabs} from '../components/TabBar';

type Status = {
    [key in Tabs]: boolean;
};

const settingsHasAnError = (errors: CatalogFormErrors): boolean => {
    return errors.find(error => error.propertyPath === '[enabled]') !== undefined;
};

const productSelectionCriteriaHasAnError = (errors: CatalogFormErrors): boolean => {
    return errors.find(error => error.propertyPath.startsWith('[product_selection_criteria]')) !== undefined;
};

export const getTabsValidationStatus = (errors: CatalogFormErrors): Status => {
    return {
        [Tabs.SETTINGS]: settingsHasAnError(errors),
        [Tabs.PRODUCT_SELECTION]: productSelectionCriteriaHasAnError(errors),
    };
};
