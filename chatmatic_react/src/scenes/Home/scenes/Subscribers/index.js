import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
import Swal from 'sweetalert2';
import { toastr } from 'react-redux-toastr';

/** import components */
import SubscriberList from './components/SubscriberList';
import ChatWidget from './components/ChatWidget';
import SummaryWidget from './components/SummaryWidget/SummaryWidget';

/** Import Actions */
import { getTags } from 'services/tags/actions';

/** Import Css */
import './styles.css';
import ChatLog from './components/ChatLog';
import UserTags from './components/UserTags';
import SubscribedCampaigns from './components/SubscribedCampaigns';

class Subscribers extends React.Component {
  componentDidMount() {
    this.props.actions.getTags(this.props.match.params.id);
  }

  UNSAFE_componentWillReceiveProps(nextProps) {
    if (nextProps.loadingTags) {
      Swal({
        title: 'Please wait...',
        text: 'Fetching a list of subscribers in this fan page...',
        onOpen: () => {
          Swal.showLoading();
        },
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false
      });
    } else if (this.props.loading || this.props.loadingTags) {
      Swal.close();

      if (nextProps.error) {
        toastr.error(nextProps.error);
      }

      if (nextProps.errorTags) {
        toastr.error(nextProps.errorTags);
      }
    }
  }

  render() {
    return (
      <div className="d-flex  p-0 subscribers-container">
        <div
          className="d-flex px-0 h-100 subscribers-list-container"
          data-aos="fade"
          data-aos-delay=""
          style={{ flex: '1 1 0', width: '0' }}
        >
          <SubscriberList />
        </div>
        <div
          className="d-flex  px-0 ml-2 mr-2 chatbox-container"
          data-aos="fade"
          data-aos-delay=""
        >
          <div className="tiles-sec tiles-sec-1">
            <div className="tiles-1">
              <div className="g-item g-small">
                  {!!this.props.activeSubscriberId && <SummaryWidget data-aos="fade"
                  data-aos-delay="" />}
              </div>
              <div className="g-item g-large">
                {!!this.props.activeSubscriberId && <ChatWidget data-aos="fade" data-aos-delay="" />}
              </div>
              <div className="g-item">
                {!!this.props.activeSubscriberId && <ChatLog />}
              </div>
              <div className="g-item">
                {!!this.props.activeSubscriberId && <UserTags />}
              </div>
              <div className="g-item">
                {!!this.props.activeSubscriberId && <SubscribedCampaigns />}
              </div>
            </div>
          </div>
        </div>


      </div>
    );
  }
}

Subscribers.propTypes = {
  error: PropTypes.string,
  loadingTags: PropTypes.bool.isRequired,
  errorTags: PropTypes.string,
  activeSubscriberId: PropTypes.number,
  actions: PropTypes.object.isRequired
};

const mapStateToProps = (state, props) => ({
  error: state.default.subscribers.error,
  loadingTags: state.default.settings.tags.loading,
  errorTags: state.default.settings.tags.error,
  activeSubscriberId: state.default.subscribers.activeSubscriberId
});

const mapDispatchToProps = dispatch => ({
  actions: bindActionCreators(
    {
      getTags
    },
    dispatch
  )
});

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(Subscribers);
