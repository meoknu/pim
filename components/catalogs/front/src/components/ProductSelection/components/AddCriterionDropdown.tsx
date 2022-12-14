import React, {FC, useCallback, useMemo, useState} from 'react';
import {Button, Dropdown, GroupsIllustration, Search} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {AnyCriterionState} from '../models/Criterion';
import {useProductSelectionContext} from '../contexts/ProductSelectionContext';
import {ProductSelectionActions} from '../reducers/ProductSelectionReducer';
import {useCriteriaRegistry} from '../hooks/useCriteriaRegistry';
import {generateRandomId} from '../utils/generateRandomId';

type Factory = {
    label: string;
    factory: () => AnyCriterionState;
};

type SectionProps = {
    label: string;
    factories: Factory[];
};

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const Section = React.forwardRef<HTMLDivElement, SectionProps>(({label, factories}, _ref) => {
    const dispatch = useProductSelectionContext();

    const handleNewCriterion = useCallback(
        (state: AnyCriterionState) => {
            dispatch({
                type: ProductSelectionActions.ADD_CRITERION,
                id: generateRandomId(),
                state: state,
            });
        },
        [dispatch]
    );

    if (0 === factories.length) {
        return null;
    }

    return (
        <>
            <Dropdown.Section>{label}</Dropdown.Section>
            {factories.map((factory, i) => (
                <Dropdown.Item key={i} onClick={() => handleNewCriterion(factory.factory())}>
                    {factory.label}
                </Dropdown.Item>
            ))}
        </>
    );
});

const AddCriterionDropdown: FC<{}> = () => {
    const translate = useTranslate();
    const [isOpen, setIsOpen] = useState<boolean>(false);
    const [search, setSearch] = useState<string>('');
    const {system} = useCriteriaRegistry();

    const systemCriterionFactories: Factory[] = useMemo(() => {
        const regex = new RegExp(search, 'i');

        return system.filter(factory => factory.label.match(regex));
    }, [system, search]);

    return (
        <Dropdown>
            <Button onClick={() => setIsOpen(true)} ghost={true} level='tertiary'>
                {translate('akeneo_catalogs.product_selection.add_criteria.label')}
            </Button>
            {isOpen && (
                <Dropdown.Overlay onClose={() => setIsOpen(false)} verticalPosition='down'>
                    <Dropdown.Header>
                        <Search
                            onSearchChange={setSearch}
                            placeholder={translate('akeneo_catalogs.product_selection.add_criteria.search')}
                            searchValue={search}
                            title={translate('akeneo_catalogs.product_selection.add_criteria.search')}
                        />
                    </Dropdown.Header>
                    <Dropdown.ItemCollection
                        noResultIllustration={<GroupsIllustration />}
                        noResultTitle={translate('akeneo_catalogs.product_selection.add_criteria.no_results')}
                    >
                        <Section
                            label={translate('akeneo_catalogs.product_selection.add_criteria.section_system')}
                            factories={systemCriterionFactories}
                        />
                    </Dropdown.ItemCollection>
                </Dropdown.Overlay>
            )}
        </Dropdown>
    );
};

export {AddCriterionDropdown};
