import { put, takeLatest, call } from 'redux-saga/effects';

import * as campaignsApi from './campaignsApi';
import {
    getPageCampaignsSucceed,
    getPageCampaignsFailed,
    deleteCampaignSucceed,
    deleteCampaignFailed,
    getCampaignInfoSucceed,
    getCampaignInfoFailed
} from './campaignsActions';
import { convertObjectKeyToCamelCase, errorMsg } from '../utils';

export function* campaignsSubscriber() {
    yield takeLatest('GET_PAGE_CAMPAIGNS', getPageCampaigns);

    yield takeLatest('DELETE_CAMPAIGN', deletePageCampign);

    yield takeLatest('GET_CAMPAIGN_INFO', getCampaignInfo);
}

export function* getPageCampaigns({ payload: { pageId } }) {
    try {
        const response = yield call(campaignsApi.getPageCampaigns, pageId);
        // console.log('response', response);

        yield put(
            getPageCampaignsSucceed(
                convertObjectKeyToCamelCase(response.data.workflow_triggers.reverse())
            )
        );
    } catch (error) {
        yield put(getPageCampaignsFailed(errorMsg(error)));
    }
}

export function* deletePageCampign({ payload: { pageId, campaignId } }) {
    try {
        yield call(campaignsApi.deleteCampaign, pageId, campaignId);

        yield put(deleteCampaignSucceed(campaignId));
    } catch (error) {
        yield put(deleteCampaignFailed(errorMsg(error)));
    }
}

export function* getCampaignInfo({ payload: { publicId } }) {
    try {
        const response = yield call(campaignsApi.getCampaignInfo, publicId);

        yield put(
            getCampaignInfoSucceed(
                convertObjectKeyToCamelCase(response.data.campaign)
            )
        );
    } catch (error) {
        yield put(getCampaignInfoFailed(errorMsg(error)));
    }
}
