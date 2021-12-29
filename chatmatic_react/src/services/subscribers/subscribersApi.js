import { wrapRequest, xapi, snakeCaseKeys } from '../utils';

export const getPageSubscribersBroadcastCount = wrapRequest(
  async (pageId, broadcast_type, filters) =>
    xapi(false).post(`pages/${pageId}/broadcasts/subscriber-count`, {
      params: {
        broadcast_type,
        filters
      }
    })
);
export const getPageSubscribers = wrapRequest(
  async (pageId, extended, page, per_page) =>
    xapi(false).get(`pages/${pageId}/subscribers`, {
      params: {
        extended,
        per_page,
        page
      }
    })
);

export const getSubscribersHistory = wrapRequest(async (pageId, recentDays) =>
  xapi(false).get(`pages/${pageId}/subscribers-history`, {
    params: {
      recent_days: recentDays
    }
  })
);

export const updateActiveSubscriber = wrapRequest(
  async (pageId, subscriberId, activeStatus) =>
    xapi().patch(`pages/${pageId}/subscribers/${subscriberId}/live-chat`, {
      params: {
        active_status: activeStatus
      }
    })
);

export const getSubscriberInfo = wrapRequest(async (pageId, subscriberId) =>
  xapi(false).get(`pages/${pageId}/subscribers/${subscriberId}`)
);

export const updateSubscriberInfo = wrapRequest(
  async (pageId, subscriberId, data) =>
    xapi(false).patch(`pages/${pageId}/subscribers/${subscriberId}`, {
      ...snakeCaseKeys(data)
    })
);

export const getExportSubscribers = wrapRequest(async pageId =>
  xapi(false).get(`pages/${pageId}/subscribers/export`)
);
