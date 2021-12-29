import React from "react";
import PropTypes from "prop-types";
import { connect } from "react-redux";
import { withRouter } from "react-router-dom";
import { Link } from "react-router-dom";
import { getPageFromUrl } from "services/pages/selector";
import { Modal, ModalBody, ModalHeader, ModalFooter } from "components";
class PurchaseLicense extends React.Component {
    state = { isModalOpen: false };

    componentDidMount() {
        this._updateModalOpen();
    }

    componentDidUpdate(prevProps) {
        //this._updateModalOpen();
        const { isSumoUser, location, page } = this.props;
        if (
            prevProps.location !== location ||
            prevProps.isSumoUser !== isSumoUser ||
            JSON.stringify(prevProps.page) != JSON.stringify(page)
        ) {
            this._updateModalOpen();
        }
    }
    _updateModalOpen = () => {
        const { isSumoUser, match, location, page } = this.props;
        const pageId = match.params.id;
        const isModalOpen =
            page &&
            page.subscribers > 250 &&
            !page.licensed &&
            location.pathname.startsWith(`/page/${pageId}`) &&
            !location.pathname.startsWith(`/page/${pageId}/settings`);
        this.setState({ isModalOpen: isModalOpen });
    };

    modalFooter = () => {
        const { match } = this.props;
        const pageId = match.params.id;

        return (
            <ModalFooter>
                <Link
                    to={`/page/${pageId}/settings/billing`}
                    className="btn btn-primary w-100"
                >
                    Upgrade
                </Link>
            </ModalFooter>
        );
    };

    render() {
        const { isSumoUser, match, page } = this.props;
        const { isModalOpen } = this.state;
        const pageId = match.params.id;
        if (isSumoUser) {
            return (
                <Modal isOpen={isModalOpen}>
                    <ModalHeader>AppSumo User</ModalHeader>
                    <ModalBody>
                        <p>
                            Awesome! It looks like you’re growing! Congrats! You
                            currently have {page.subscribers} subscribers and
                            our system requires a license for this page to
                            continue running and collecting subscribers.
                        </p>
                        <p>
                            Please click upgrade below to license this page to
                            keep all automations running
                        </p>
                    </ModalBody>
                    {this.modalFooter()}
                </Modal>
            );
        }
        return (
            <Modal isOpen={isModalOpen}>
                <ModalHeader>Max Subscribers reached</ModalHeader>
                <ModalBody>
                    <p>
                        Awesome! It looks like you’re growing! Congrats! You
                        currently have {page.subscribers} subscribers and our
                        system requires a license for this page to continue
                        running and collecting subscribers.
                    </p>
                    <p>
                        Please click upgrade below to license this page to keep
                        all automations running
                    </p>
                </ModalBody>
                {this.modalFooter()}
            </Modal>
        );
    }
}
PurchaseLicense.propTypes = {
    isSumoUser: PropTypes.bool.isRequired,
    page: PropTypes.object.isRequired
};
const mapStateToProps = (state, props) => ({
    isSumoUser: state.default.pages.isSumoUser,
    page: getPageFromUrl(state, props)
});
export default withRouter(connect(mapStateToProps, {})(PurchaseLicense));
