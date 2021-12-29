import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import { withRouter } from 'react-router-dom';
import PropTypes from 'prop-types';
import Swal from 'sweetalert2';
import { CSVLink } from 'react-csv';
import classnames from 'classnames';
import InfiniteScroll from 'react-infinite-scroller';
import Subscriber from '../Subscriber';
import { Collapse } from 'components';
import uuidv1 from 'uuid/v1';
import { Scrollbars } from 'react-custom-scrollbars';
import {
  getSubscribers,
  getSubscribersState
} from 'services/subscribers/selector';
import {
  clearPageSubscribers,
  getPageSubscribers,
  updateActiveSubscriber,
  getExportSubscribers
} from 'services/subscribers/subscribersActions';

import './styles.css';

class SubscriberList extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      action: '',
      subscriberSearch: '',
      debounceTimer: null,
      subscribers: this.props.subscribers || [],
      isFilteringSubscribers: false,
      exportSubscribers: []
    };
    this.searchRef = React.createRef();
    this.buttonRef = React.createRef();
  }

  componentDidMount() {
    const { actions, pageId, loading } = this.props;
    actions.clearPageSubscribers();
    actions.getPageSubscribers(pageId, false);
    document.addEventListener('mousedown', this.handleSearchBlur);
  }

  componentWillUnmount() {
    this.props.actions.clearPageSubscribers();
    document.removeEventListener('mousedown', this.handleSearchBlur);
  }

  UNSAFE_componentWillReceiveProps(nextProps, nextState) {
    if (nextProps.loading) {
      Swal({
        title: 'Please wait...',
        text:
          this.state.action == 'getSubscribers'
            ? 'Fetching more subscribers'
            : 'We are fetching a subscriber detailed info...',
        onOpen: () => {
          Swal.showLoading();
        },
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false
      });

      return <div />;
    } else if (this.props.loading) {
      Swal.close();

      if (nextProps.error) {
        Swal({
          title: 'Subscriber Detail Info Fetching Error',
          text: nextProps.error,
          type: 'error',
          showCancelButton: true,
          cancelButtonText: 'Close'
        });
      }
    }
    if (nextProps.loadingExportSubscribers) {
      Swal({
        title: 'Please wait...',
        text: 'We are exporting subscribers data',
        onOpen: () => {
          Swal.showLoading();
        },
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false
      });
    } else if (this.props.loadingExportSubscribers) {
      Swal.close();
      console.log(nextProps)
      if (nextProps.error) {
        Swal({
          title: 'Error on Exporting Subscribers',
          text: nextProps.error,
          type: 'error',
          showCancelButton: true,
          cancelButtonText: 'Close'
        });
      } else {
        const csvData = nextProps.exportSubscribers.map((subscriber, index) => {
          let subscriberData = {
            index: index + 1,
            firstName: subscriber.firstName,
            lastName: subscriber.lastName,
            phone: subscriber.phone,
            email: subscriber.email,
            location: subscriber.location,
            psid: subscriber.psid
          };
          subscriber.tags.forEach((tag, index) => {
            subscriberData[`tag${index + 1}`] = tag.value;
          });
          subscriberData[`Attributes`] = '';
          subscriber.customFieldResponses.forEach((response, index) => {
            let temp = '<' + response.fieldName + '>' + '-' + response.response + ' | ';
            subscriberData[`Attributes`] += temp;
          });
          return subscriberData;
        });
        this.setState({ exportSubscribers: csvData });
      }
    }
  }

  componentDidUpdate(prevProps, prevState) {
    const { subscribers } = this.props;
    if (
      prevProps.loadingExportSubscribers &&
      !this.props.loadingExportSubscribers &&
      !this.props.error
    ) {
      this.refs.csv.link.click();
    }

    if (subscribers && subscribers.length !== prevProps.subscribers.length) {
      this.setState({ action: '', subscribers: subscribers });
    }
  }

  _filterSubscribers = () => {
    this.props.actions.updateActiveSubscriber(
      this.props.match.params.id,
      null,
      true
    );

    const { subscriberSearch } = this.state;
    const filteredSubscribers = this.props.subscribers.filter(subscriber => {
      if (
        subscriber.firstName &&
        subscriber.firstName
          .toLowerCase()
          .includes(subscriberSearch.toLowerCase())
      ) {
        return true;
      }

      return !!(
        subscriber.lastName &&
        subscriber.lastName
          .toLowerCase()
          .includes(subscriberSearch.toLowerCase())
      );
    });

    this.setState({ subscribers: filteredSubscribers });
  };

  _loadMoreSubscribers = page => {
    const { actions, pageId, paging, loading } = this.props;
    if (loading) {
      return;
    }
    let nextPage = paging.currentPage + 1;
    this.setState({ action: 'getSubscribers' });
    actions.getPageSubscribers(pageId, false, nextPage);
  };

  countDebounce = e => {
    e.persist();
    let { debounceTimer } = this.state;

    clearTimeout(debounceTimer);
    this.setState({ subscriberSearch: e.target.value });

    debounceTimer = setTimeout(() => {
      this._filterSubscribers();
    }, 500);
    this.setState({ debounceTimer });
  };

  _loadExportSubscribers = () => {
    this.props.actions.getExportSubscribers(this.props.match.params.id);
  };

  handleSearchBlur = (event) => {
    if (this.searchRef && !this.searchRef.current.contains(event.target)) {
      setTimeout(() => {
        this.setState({ isFilteringSubscribers: false });
      }, 500)
    }
  }

  render() {
    const { isLoading, paging } = this.props;
    const loadingMessage = (
      <h5 key={uuidv1()} className="px-3">
        Loading...
      </h5>
    );

    return (
      <div className="w-100 subscribers-list-wrapper d-flex flex-column" >
        <div className={classnames('d-flex flex-column align-items-center subscribers-header', {
            'show-btn-export': this.state.isFilteringSubscribers
          })} >
          <div className="filter-wrapper" ref={this.searchRef}>
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M7.04606 0C3.16097 0 0 3.16097 0 7.04606C0 10.9314 3.16097 14.0921 7.04606 14.0921C10.9314 14.0921 14.0921 10.9314 14.0921 7.04606C14.0921 3.16097 10.9314 0 7.04606 0ZM7.04606 12.7913C3.87816 12.7913 1.30081 10.214 1.30081 7.04609C1.30081 3.87819 3.87816 1.30081 7.04606 1.30081C10.214 1.30081 12.7913 3.87816 12.7913 7.04606C12.7913 10.214 10.214 12.7913 7.04606 12.7913Z" fill="#898996"/>
              <path d="M15.808 14.8893L12.079 11.1603C11.8249 10.9062 11.4134 10.9062 11.1593 11.1603C10.9052 11.4142 10.9052 11.8261 11.1593 12.08L14.8883 15.809C15.0154 15.936 15.1817 15.9996 15.3482 15.9996C15.5145 15.9996 15.681 15.936 15.808 15.809C16.0621 15.5551 16.0621 15.1432 15.808 14.8893Z" fill="#898996"/>
            </svg>

            <input
              type="text"
              placeholder="Search subscribers by name"
              className="form-control rounded bg-white"
              onChange={this.countDebounce}
              onFocus={() => this.setState({ isFilteringSubscribers: true })}
              
              name="subscriberSearch"
            />
          </div>
          <Collapse isOpen={this.state.isFilteringSubscribers} className="p-3 export-wrapper">
            <CSVLink
              ref="csv"
              data={this.state.exportSubscribers}
              filename={'subscribers.csv'}
              className="btn-export-csv d-none"
            >
              Export Subscribers
            </CSVLink>
            <button
              className="btn btn-link btn-export-csv"
              onClick={this._loadExportSubscribers}
              ref={this.buttonRef}
            >
              Export Subscribers
            </button>
          </Collapse>
        </div>
        <div
          id="subscriberScrollContainer"
          ref={ref => (this.scrollParentRef = ref)}
          className={classnames('w-100 subscriber-list', {
            'show-btn-export': this.state.isFilteringSubscribers
          })}
        >
          <Scrollbars 
              renderTrackVertical={props => { console.log(props); return (<div {...props} className="track-vertical"/>)}}
              renderThumbVertical={props => <div {...props} className="thumb-vertical"/>}
              style={{ width: '100%', height: '100%' }}>
              <InfiniteScroll
                pageStart={1}
                initialLoad={false}
                useWindow={false}
                hasMore={
                  !isLoading &&
                  !this.state.subscriberSearch &&
                  this.state.subscribers.length > 0 &&
                  this.state.subscribers.length !== (paging && paging.total)
                }
                loadMore={this._loadMoreSubscribers}
                loader={loadingMessage}
                getScrollParent={() => this.scrollParentRef}
              >
                {this.state.subscribers.map((subscriber, index) => (
                  <Subscriber key={index} subscriberId={subscriber.uid} />
                ))}
              </InfiniteScroll>
          </Scrollbars>
          
        </div>
      </div>
    );
  }
}

SubscriberList.propTypes = {
  activeSubscriberId: PropTypes.number,
  pageId: PropTypes.number.isRequired,
  paging: PropTypes.object,
  subscribers: PropTypes.array.isRequired,
  exportSubscribers: PropTypes.array.isRequired,
  loading: PropTypes.bool.isRequired,
  loadingExportSubscribers: PropTypes.bool.isRequired,
  error: PropTypes.any
};

const mapStateToProps = (state, props) => ({
  activeSubscriberId: state.default.subscribers.activeSubscriberId,
  pageId: (props && parseInt(props.match.params.id)) || 0,
  subscribers: getSubscribers(state),
  loading: getSubscribersState(state).loading,
  loadingExportSubscribers: getSubscribersState(state).loadingExportSubscribers,
  paging: getSubscribersState(state).paging,
  exportSubscribers: getSubscribersState(state).exportSubscribers,
  error: getSubscribersState(state).error
});

const mapDispatchToProps = dispatch => ({
  actions: bindActionCreators(
    {
      clearPageSubscribers,
      getPageSubscribers,
      updateActiveSubscriber,
      getExportSubscribers
    },
    dispatch
  )
});

export default withRouter(
  connect(
    mapStateToProps,
    mapDispatchToProps
  )(SubscriberList)
);
