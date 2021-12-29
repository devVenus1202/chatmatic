import React, { Component } from 'react'
import PropTypes from 'prop-types'
import moment from 'moment';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { withRouter } from 'react-router-dom';
import Swal from 'sweetalert2';

import { getTagsState } from 'services/tags/selector';
import { getActiveSubscriber } from 'services/subscribers/selector';
import { updateSubscriberInfo } from 'services/subscribers/subscribersActions';
import { addTag } from 'services/tags/actions';
import calendarImg from 'assets/images/icon-calendar.svg';
import './styles.scss'

class ChatLog extends Component {
    // static propTypes = {
    //     prop: PropTypes
    // }

    render() {
        return (
            <div className="card bottom-left-radius">
                <div className="w-100 summary-container">
                    <div className="d-flex justify-content-between message-info-header ">
                        <h4>Total Messages</h4>
                        <div className="d-flex align-items-center">
                            <div className="d-flex flex-column">
                                {/* <label>LAST ENGAGEMENT</label> */}
                                <span className="pr-3">
                                    {moment(this.props.subscriber.lastEngagementUtc).format(
                                        'DD.MM.YYYY'
                                    )}
                                </span>
                            </div>
                            <img src={calendarImg} alt="" />
                        </div>
                    </div>
                    <hr/>
                    <div className="d-flex justify-content-between message-info">
                        <div className="d-flex flex-column">
                        <label>Message Sent</label>
                        <span>{this.props.subscriber.messagesSent || 0}</span>
                        </div>
                        <div className="d-flex flex-column">
                        <label>Message Read</label>
                        <span>{this.props.subscriber.messagesRead || 0}</span>
                        </div>
                        <div className="d-flex flex-column">
                        <label>Total Clicks</label>
                        <span>{this.props.subscriber.totalClicks || 0}</span>
                        </div>
                    </div>
                </div>
            
            </div>
        )
    }
}

const mapStateToProps = state => ({
    subscriber: getActiveSubscriber(state),
    pageTags: getTagsState(state).tags,
    loading: state.default.subscribers.loading,
    error: state.default.subscribers.error,
    addingTag: state.default.settings.tags.loading,
    addingTagError: state.default.settings.tags.error
});

const mapDispatchToProps = dispatch => ({
actions: bindActionCreators(
    {
    updateSubscriberInfo,
    addTag
    },
    dispatch
)
});

export default withRouter(
    connect(
        mapStateToProps,
        mapDispatchToProps
    )(ChatLog)
);
  
