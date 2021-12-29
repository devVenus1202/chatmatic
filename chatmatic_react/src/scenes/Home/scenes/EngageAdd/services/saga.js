import { put, takeLatest, call, select, takeEvery } from 'redux-saga/effects';

import * as apis from './api';
import {
    addEngageSucceed,
    addEngageFailed,
    updateEngageSucceed,
    updateEngageFailed,
    fileUploadSucceed,
    fileUploadFailed,
    pageFileUploadSucceed,
    pageFileUploadFailed,
    saveBroadcastSucceed,
    saveBroadcastFailed
} from './actions';
import {
    convertObjectKeyToCamelCase,
    errorMsg,
    formatBroadcast
} from 'services/utils';
import { getPageWorkflowTriggers, getPageWorkflow } from 'services/workflows/workflowsActions';

export function* engageAddSubscriber() {
    yield takeLatest('ADD_ENGAGE', addPageEngage);
    yield takeLatest('UPDATE_ENGAGE', updatePageEngage);
    yield takeEvery('FILE_UPLOAD', fileUpload);
    yield takeEvery('SAVE_BROADCAST', updateBroadcast);
    yield takeEvery('PAGE_FILE_UPLOAD', pageFileUpload);
}

export function* addPageEngage({ payload: { pageId, engage } }) {
    try {
        // debugger;
        const response = yield call(apis.addEngage, pageId, engage);

        yield put(
            addEngageSucceed(convertObjectKeyToCamelCase(response.data).uid)
        );
    } catch (error) {
        yield put(addEngageFailed(errorMsg(error)));
    }
}

export function* updatePageEngage({ payload: { pageId, engage } }) {
    try {
        yield call(apis.updateEngage, pageId, engage);


        yield put(getPageWorkflowTriggers(pageId));
        yield put(getPageWorkflow(pageId, engage.uid));
        yield put(updateEngageSucceed(engage.uid));
    } catch (error) {
        yield put(updateEngageFailed(errorMsg(error)));
    }
}

export function* fileUpload({
    payload: { pageId, stepUid, itemIndex, src, carouselItemIndex, url }
}) {
    try {
        const engageAddState = (yield select()).default.scenes.engageAdd;
        const activeStepItems = engageAddState.steps.find(
            step => step.stepUid === stepUid
        ).items;

        const fileType =
            activeStepItems[itemIndex].type === 'audio'
                ? 'audio'
                : activeStepItems[itemIndex].type === 'video'
                ? 'video'
                : 'image';

        const response = yield call(
            apis.fileUpload,
            pageId,
            fileType,
            src,
            stepUid,
            itemIndex,
            url
        );

        yield put(
            fileUploadSucceed(
                stepUid,
                itemIndex,
                response.data.uid,
                response.data.url,
                carouselItemIndex
            )
        );
    } catch (error) {
        yield put(fileUploadFailed(errorMsg(error)));
    }
}

export function* pageFileUpload({
    payload: { pageId, fileType, src, url }
}) {
    try {
        const response = yield call(
            apis.pageFileUpload,
            pageId,
            fileType,
            src,
            url
        );

        yield put(
            pageFileUploadSucceed(
                response.data.uid,
                response.data.url
            )
        );
    } catch (error) {
        yield put(pageFileUploadFailed(errorMsg(error)));
    }
}

export function* updateBroadcast({ payload: { pageId } }) {
    try {
        const broadcast = (yield select()).default.scenes.engageAdd.broadcast;
        yield call(apis.updateBroadcast, pageId, formatBroadcast(broadcast));

        yield put(saveBroadcastSucceed());
    } catch (error) {
        yield put(saveBroadcastFailed(errorMsg(error)));
    }
}
