import React, {FC} from 'react';
import {useSecurity} from '../../shared/security';
import {useTranslate} from '../../shared/translate';
import {Button} from 'akeneo-design-system';
import {useRouter} from '../../shared/router/use-router';
import {useConnectionsLimitReached} from '../../shared/hooks/use-connections-limit-reached';

export const ActivateAppButton: FC<{id: string; isConnected: boolean}> = ({id, isConnected}) => {
    const translate = useTranslate();
    const security = useSecurity();
    const generateUrl = useRouter();
    const isAuthorized = security.isGranted('akeneo_connectivity_connection_manage_apps');
    const isLimitReached = useConnectionsLimitReached();
    const isEnabled = isAuthorized && false === isLimitReached;

    const url = `#${generateUrl('akeneo_connectivity_connection_connect_apps_activate', {
        id: id,
    })}`;

    if (isConnected) {
        return (
            <Button level='primary' disabled>
                {translate('akeneo_connectivity.connection.connect.marketplace.card.connected')}
            </Button>
        );
    }

    return (
        <Button href={url} target='_blank' level='primary' disabled={!isEnabled}>
            {translate('akeneo_connectivity.connection.connect.marketplace.card.connect')}
        </Button>
    );
};
