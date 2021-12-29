import React from "react";
import { connect } from "react-redux";
import { bindActionCreators } from "redux";
import { withRouter } from "react-router-dom";
import {
    Input,
    Button,
    Icon,
    // Progress,
    Grid
} from "semantic-ui-react";
import { Button as MuiButton, Grid as MuiGrid } from "@material-ui/core";
import MuiPaper from "../../../components/MuiPaper";
import MuiTypography from "../../../components/MuiTypography";
import VideoCard from "../../../components/VideoCard";
import AddCircleOutlineIcon from "@material-ui/icons/AddCircleOutline";

import { Block, Svg } from "../Layout";
import { LineChat } from "../components/Charts";
import { AddNewPageModal } from "./components";
import { getPages } from "services/pages/pagesActions";
import "react-responsive-carousel/lib/styles/carousel.min.css";
import { Carousel } from "react-responsive-carousel";
import SearchBar from "../../../components/SearchText";
import CircleFacebookIcon from "assets/images/icon-facebook-circle.svg";
import CircleInstagramIcon from "assets/images/icon-instagram-circle.svg";
import  './styles.scss';

const fileContentStyle = {
    objectFit: "cover"
    // borderRadius: 18
};

class Dashboard extends React.Component {
    //#region life cycle
    constructor(props) {
        super(props);
        const { updates } = props;
        // const updates = [...props.updates, ...props.updates, ...props.updates];
        this.state = {
            pages: props.pages,
            allPages: props.pages,
            pageSearch: "",
            updates,
            activeUpdateIndex: 0,
            isNextUpdate: updates && updates.length > 1 ? true : false,
            isPrevUpdate: false,
            addPageModal: false
        };
    }

    componentDidMount = () => {
        // this.props.actions.getPages();
    };
    componentDidUpdate(prevProps) {
        const { pages, updates } = this.props;
        const { pageSearch } = this.state;

        if (
            JSON.stringify(prevProps.pages) != JSON.stringify(pages) ||
            JSON.stringify(updates) != JSON.stringify(prevProps.updates)
        ) {
            this.setState(
                {
                    allPages: pages,
                    addPageModal: !pages || pages.length == 0,
                    updates,
                    isNextUpdate: updates && updates.length > 1 ? true : false
                },
                () => {
                    this.onPageSearch(pageSearch);
                }
            );
        }
    }
    //#endregion

    //#region functionality
    onPageSearch = pageSearch => {
        const { allPages } = this.state;
        if (pageSearch && pageSearch.trim() !== "") {
            const pages = allPages.filter(p =>
                p.fbName.toLowerCase().includes(pageSearch.trim().toLowerCase())
            );
            this.setState({
                pageSearch,
                pages
            });
        } else {
            this.setState({
                pageSearch: "",
                pages: allPages
            });
        }
    };

    clearPageSearch = () =>
        this.setState(({ allPages }) => ({ pages: allPages, pageSearch: "" }));

    openPage = pageId => {
        this.props.history.push(`/page/${pageId}`);
    };

    prevUpdate = () => {
        const {
            updates,
            activeUpdateIndex: indexNow,
            isPrevUpdate
        } = this.state;
        if (isPrevUpdate) {
            this.setState({
                isNextUpdate: true,
                activeUpdateIndex: indexNow - 1,
                isPrevUpdate: updates && updates[indexNow - 2] ? true : false
            });
        }
    };

    nextUpdate = () => {
        const {
            updates,
            activeUpdateIndex: indexNow,
            isNextUpdate
        } = this.state;
        if (isNextUpdate) {
            this.setState({
                isNextUpdate: updates && updates[indexNow + 2] ? true : false,
                activeUpdateIndex: indexNow + 1,
                isPrevUpdate: true
            });
        }
    };

    handleCloseNewPageModal = () => {
        this.setState({ addPageModal: false });
        this.props.actions.getPages();
    };
    //#endregion

    render() {
        const {
            totalPages,
            totalSequences,
            totalSubscribers,
            totalRecentSubscribers,
            tips,
            currentUser
        } = this.props;

        const {
            pageSearch,
            updates,
            activeUpdateIndex,
            isNextUpdate,
            isPrevUpdate,
            addPageModal
        } = this.state;

        let newSubsPer = 0;
        let totalSubs = 0;
        let chartData = {};
        if (totalSubscribers && totalSubscribers.length > 0) {
            totalSubs = totalSubscribers[0].total;
            // console.log('subs', totalSubscribers[0], totalSubscribers);
            totalSubscribers.map(s => {
                chartData[s.date] = s.total;
                return true;
            });
        }
        if (
            totalSubscribers &&
            totalSubscribers.length > 0 &&
            totalRecentSubscribers
        ) {
            newSubsPer = ((totalRecentSubscribers / totalSubs) * 100).toFixed(
                0
            );
        }

        // console.log('updates', updates);
        const pages = this.state.pages.filter(p => p.isConnected);

        return (
            <Block className="main-container trigger-container dashboard-container mt-0">
                {addPageModal && (
                    <AddNewPageModal
                        open={addPageModal}
                        close={this.handleCloseNewPageModal}
                    />
                )}
                <Block className="inner-box-main">
                    <Block className="dashboard-block">
                        <Block className="dashboard-aside-left">
                            <MuiPaper full>
                                <SearchBar
                                    placeholder="Search page by name..."
                                    value={pageSearch}
                                    onChange={e =>
                                        this.onPageSearch(e.target.value)
                                    }
                                />

                                <Block className="addnewField">
                                    {pages &&
                                        pages.length > 0 &&
                                        pages.map(p => (
                                            <Block
                                                onClick={() =>
                                                    this.openPage(p.uid)
                                                }
                                                key={p.uid}
                                                className="side-listing align-items-center"
                                            >
                                                <Block className="img-circle">
                                                    <img
                                                        src={`https://graph.facebook.com/${p.fbId}/picture?type=small`}
                                                        alt="user"
                                                    />
                                                </Block>
                                                <Block className="list-text flex-grow-1">
                                                    <h3>{p.fbName}</h3>
                                                    <Block className="list-bottom">
                                                        <Block className="username">
                                                            <Icon name="users" />{" "}
                                                            {p.subscribers}
                                                            <span>
                                                                {" "}
                                                                +
                                                                {
                                                                    p.recentSubscribers
                                                                }
                                                            </span>
                                                        </Block>
                                                        <Block className="calander">
                                                            <Icon name="calendar alternate" />{" "}
                                                            {p.sequences}{" "}
                                                            {/* <span>+17</span> */}
                                                        </Block>
                                                    </Block>
                                                </Block>
                                                <Block className="page-type-icon">
                                                    {p.source === "fb" && (
                                                        <img
                                                            src={
                                                                CircleFacebookIcon
                                                            }
                                                        />
                                                    )}
                                                    {p.source === "ig" && (
                                                        <img
                                                            src={
                                                                CircleInstagramIcon
                                                            }
                                                        />
                                                    )}
                                                </Block>
                                            </Block>
                                        ))}
                                </Block>
                                <MuiButton
                                    variant="contained"
                                    color="primary"
                                    size="medium"
                                    onClick={() =>
                                        this.setState({ addPageModal: true })
                                    }
                                    className="btn plusBtn text-capitalize"
                                >
                                    <AddCircleOutlineIcon />
                                    <span class="font-size-2">
                                        Add A new fan page
                                    </span>
                                </MuiButton>
                            </MuiPaper>
                        </Block>
                        <MuiGrid container spacing={2}>
                            <MuiGrid item xs={4}>
                                <MuiPaper
                                    className="welcome-col-box"
                                    elevation={0}
                                >
                                    <MuiTypography fontColor="#6B6F87">
                                        Total Pages
                                    </MuiTypography>
                                    <MuiTypography
                                        fontSize="38px"
                                        fontWeight="bold"
                                        className="no"
                                    >
                                        {totalPages}
                                    </MuiTypography>
                                </MuiPaper>
                            </MuiGrid>
                            <MuiGrid item xs={4}>
                                <MuiPaper
                                    className="welcome-col-box"
                                    elevation={0}
                                >
                                    <MuiTypography fontColor="#6B6F87">
                                        Total Subs
                                    </MuiTypography>
                                    <MuiTypography
                                        fontSize="38px"
                                        fontWeight="bold"
                                    >
                                        {totalSubs}
                                        <MuiTypography
                                            component="span"
                                            fontSize="16px"
                                            fontColor="#F64F3D"
                                        >
                                            {" "}
                                            +{newSubsPer}%
                                        </MuiTypography>
                                    </MuiTypography>
                                </MuiPaper>
                            </MuiGrid>
                            <MuiGrid item xs={4}>
                                <MuiPaper
                                    className="welcome-col-box"
                                    elevation={0}
                                >
                                    <MuiTypography fontColor="#6B6F87">
                                        Workflows
                                    </MuiTypography>
                                    <MuiTypography
                                        fontSize="38px"
                                        fontWeight="bold"
                                    >
                                        {totalSequences}
                                    </MuiTypography>
                                </MuiPaper>
                            </MuiGrid>

                            <MuiGrid item xs={12}>
                                <MuiPaper elevation={0}>
                                    <h2>Subscribers Graphs</h2>
                                    {chartData && <LineChat data={chartData} />}
                                </MuiPaper>
                            </MuiGrid>

                            <MuiGrid item xs={6}>
                                <MuiPaper
                                    className="welcome-col-box"
                                    elevation={0}
                                >
                                    <Block className="tips-section">
                                        <h2>Tips</h2>
                                        <Block className="carousal-col">
                                            <Carousel
                                            // showThumbs={false}
                                            >
                                                {tips &&
                                                    tips.length > 0 &&
                                                    tips.map(t => (
                                                        <Block key={t.id}>
                                                            <video
                                                                className="w-100"
                                                                style={
                                                                    fileContentStyle
                                                                }
                                                                controls
                                                            >
                                                                <source
                                                                    src={t.url}
                                                                />
                                                            </video>
                                                        </Block>
                                                    ))}
                                            </Carousel>
                                        </Block>
                                    </Block>
                                </MuiPaper>
                            </MuiGrid>

                            <MuiGrid item xs={6}>
                                <MuiPaper
                                    className="welcome-col-box"
                                    elevation={0}
                                >
                                    <Block className="update-section d-flex">
                                        <h2>Updates & Changelog</h2>
                                        <Block className="update-section-inner">
                                            {updates &&
                                                updates[activeUpdateIndex] && (
                                                    <div
                                                        dangerouslySetInnerHTML={{
                                                            __html:
                                                                updates[
                                                                    activeUpdateIndex
                                                                ].content
                                                        }}
                                                    ></div>
                                                )}
                                            {/* <h4 className="titlesm">
                                                    New Dashboard!
                                                </h4>
                                                <p>
                                                    Feature Update One
                                                    listing lorem ipsum
                                                    dolor sit amet.
                                                </p>
                                                <List>
                                                    <List.Item>
                                                        - Feature Update One
                                                    </List.Item>
                                                    <List.Item>
                                                        - Feature Update One
                                                    </List.Item>
                                                    <List.Item>
                                                        - Feature Update One
                                                    </List.Item>
                                                </List> */}
                                            {/* <Block className="update-btn">
                                                    <Button className="primary">
                                                        View Update{' '}
                                                        <Icon name="arrow right" />
                                                    </Button>
                                                </Block> */}
                                        </Block>
                                        <Block className="c-update-dash-btns mt-auto">
                                            <Button
                                                onClick={this.prevUpdate}
                                                disabled={!isPrevUpdate}
                                                className="prev-btn arrow-btn"
                                            >
                                                <Icon name="arrow left" />{" "}
                                                previous
                                            </Button>
                                            <Button
                                                onClick={this.nextUpdate}
                                                disabled={!isNextUpdate}
                                                className="next-btn arrow-btn float-right mt-1"
                                            >
                                                next <Icon name="arrow right" />
                                            </Button>
                                        </Block>
                                    </Block>
                                </MuiPaper>
                            </MuiGrid>
                        </MuiGrid>
                        <Block className="dashboard-aside-right">
                            <MuiGrid container spacing={2}>
                                <MuiGrid item xs={12}>
                                    <MuiPaper>
                                        <h2>
                                            Trainging Videos
                                        </h2>
                                        <MuiGrid
                                            container
                                            spacing={2}
                                            alignItems="center"
                                        >
                                            <MuiGrid item xs={5}>
                                                <VideoCard src="https://www.youtube.com/watch?v=HbE9ps9wzI8"></VideoCard>
                                            </MuiGrid>
                                            <MuiGrid item xs={7}>
                                                <MuiTypography fontSize="18px">
                                                    Get started here
                                                </MuiTypography>
                                            </MuiGrid>
                                            <MuiGrid item xs={5}>
                                                <VideoCard src="https://youtu.be/ntbC95XzCTU"></VideoCard>
                                            </MuiGrid>
                                            <MuiGrid item xs={7}>
                                                <MuiTypography fontSize="18px">
                                                    Get started here
                                                </MuiTypography>
                                            </MuiGrid>
                                        </MuiGrid>
                                    </MuiPaper>
                                </MuiGrid>
                                <MuiGrid item xs={12}>
                                    <MuiPaper>
                                        <div>
                                            <h2>
                                                Latest Training
                                            </h2>
                                        </div>
                                        <VideoCard src="https://youtu.be/Nx2Dw5duFDk"></VideoCard>
                                        <MuiTypography
                                            fontSize="18px"
                                            fontWeight="500"
                                            component="label"
                                        >
                                            Get started here
                                        </MuiTypography>
                                        <MuiTypography>
                                            8 may, 2021
                                        </MuiTypography>
                                    </MuiPaper>
                                </MuiGrid>
                                <MuiGrid item xs={12}>
                                    <MuiPaper>
                                        <svg
                                            width="23"
                                            height="23"
                                            viewBox="0 0 23 23"
                                            fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                d="M13.2801 23V12.5094H16.8L17.3281 8.4198H13.2801V5.80919C13.2801 4.62553 13.6075 3.81888 15.3068 3.81888L17.4705 3.81799V0.160114C17.0964 0.111487 15.8119 0 14.3169 0C11.1951 0 9.05787 1.90551 9.05787 5.40416V8.4198H5.52734V12.5094H9.05787V23H13.2801Z"
                                                fill="#3D5B96"
                                            />
                                        </svg>
                                        <MuiTypography component="span">
                                            &nbsp;&nbsp;Join Our Group
                                        </MuiTypography>
                                    </MuiPaper>
                                </MuiGrid>
                                <MuiGrid item xs={12}>
                                    <MuiPaper>
                                        <svg
                                            width="20"
                                            height="20"
                                            viewBox="0 0 20 20"
                                            fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <g clip-path="url(#clip0)">
                                                <path
                                                    d="M19.5879 5.19897C19.3574 4.34219 18.6819 3.66684 17.8252 3.43612C16.2602 3.00781 9.99981 3.00781 9.99981 3.00781C9.99981 3.00781 3.73961 3.00781 2.17452 3.4198C1.33438 3.65036 0.642392 4.34234 0.411833 5.19897C0 6.7639 0 10.0094 0 10.0094C0 10.0094 0 13.2713 0.411833 14.8199C0.642545 15.6765 1.3179 16.352 2.17467 16.5827C3.75609 17.0111 9.99996 17.0111 9.99996 17.0111C9.99996 17.0111 16.2602 17.0111 17.8252 16.5991C18.682 16.3685 19.3574 15.693 19.5881 14.8364C19.9999 13.2713 19.9999 10.0259 19.9999 10.0259C19.9999 10.0259 20.0164 6.7639 19.5879 5.19897Z"
                                                    fill="#FF0000"
                                                />
                                                <path
                                                    d="M8.00781 13.0084L13.2137 10.0101L8.00781 7.01172V13.0084Z"
                                                    fill="white"
                                                />
                                            </g>
                                            <defs>
                                                <clipPath id="clip0">
                                                    <rect
                                                        width="20"
                                                        height="20"
                                                        fill="white"
                                                    />
                                                </clipPath>
                                            </defs>
                                        </svg>
                                        <MuiTypography component="span">
                                            &nbsp;&nbsp;Subscribe On Youtube
                                        </MuiTypography>
                                    </MuiPaper>
                                </MuiGrid>
                                <MuiGrid item xs={12}>
                                    <MuiPaper>
                                        <svg
                                            width="21"
                                            height="21"
                                            viewBox="0 0 21 21"
                                            fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <g clip-path="url(#clip0)">
                                                <path
                                                    d="M10.4998 0.000976562C4.70092 0.000976562 0 4.7019 0 10.5008C0 16.2996 4.70092 21.0006 10.4998 21.0006C16.2987 21.0006 20.9996 16.2996 20.9996 10.5008C20.9996 4.7019 16.2987 0.000976562 10.4998 0.000976562ZM10.4998 15.4669C7.757 15.4669 5.53362 13.2436 5.53362 10.5008C5.53362 7.75797 7.757 5.5346 10.4998 5.5346C13.2426 5.5346 15.466 7.75797 15.466 10.5008C15.466 13.2436 13.2426 15.4669 10.4998 15.4669Z"
                                                    fill="#E8E8E8"
                                                />
                                                <path
                                                    d="M1.58911 10.5008C1.58911 4.96932 5.86659 0.43685 11.2943 0.0305488C11.0321 0.0108203 10.7672 0.000976562 10.4998 0.000976562C4.70092 0.000976563 0 4.7019 0 10.5008C0 16.2996 4.70092 21.0006 10.4998 21.0006C10.7672 21.0006 11.0321 20.9907 11.2943 20.971C5.86659 20.5647 1.58911 16.0322 1.58911 10.5008ZM12.0889 5.5346C11.8185 5.5346 11.5532 5.55601 11.2943 5.59846C13.6596 5.97814 15.466 8.02839 15.466 10.5008C15.466 12.9732 13.6596 15.0234 11.2943 15.4031C11.5532 15.4455 11.8185 15.4669 12.0889 15.4669C14.8317 15.4669 17.0551 13.2436 17.0551 10.5008C17.0551 7.75797 14.8317 5.5346 12.0889 5.5346Z"
                                                    fill="#C1C0BF"
                                                />
                                                <path
                                                    d="M21.0031 10.4998C19.7247 10.0617 18.3683 9.9362 17.0449 10.1226C16.5122 10.1976 15.9846 10.3232 15.4699 10.4998C15.4699 12.9721 13.6635 15.0219 11.2987 15.4021H11.2983C11.0394 15.4445 10.7742 15.4659 10.5037 15.4659H10.5033C9.90336 17.2175 9.88962 19.1151 10.4613 20.8736C10.475 20.9156 10.4892 20.9571 10.5033 20.9991V20.9995H10.5037C10.5505 20.9995 10.5976 20.9991 10.6443 20.9986C10.682 20.9982 10.7201 20.9974 10.7579 20.9965C10.8089 20.9952 10.8594 20.9939 10.9096 20.9918C10.9529 20.9901 10.9957 20.9884 11.039 20.9862C11.0429 20.9858 11.0472 20.9858 11.0511 20.9854C11.0935 20.9832 11.1359 20.9811 11.1779 20.9781C11.2182 20.9759 11.2585 20.9729 11.2983 20.9699C16.726 20.5637 21.0035 16.0312 21.0035 10.4998H21.0031ZM10.5033 0.000410156V0C4.70442 0.000410156 0.00390625 4.70088 0.00390625 10.4998H0.00431641C0.525461 10.321 1.05985 10.1946 1.59941 10.1196C2.91597 9.93747 4.26506 10.0643 5.53749 10.4998C5.53749 7.75696 7.76045 5.53399 10.5033 5.53358C9.90327 3.78205 9.88953 1.88438 10.4613 0.125959C10.475 0.083959 10.4891 0.0424102 10.5033 0.000410156Z"
                                                    fill="#FF605D"
                                                />
                                                <path
                                                    d="M10.5033 0.000410156V0C4.70442 0.000410156 0.00390625 4.70088 0.00390625 10.4998H0.00431641C0.525461 10.321 1.05985 10.1946 1.59941 10.1196C1.78033 5.04714 5.55894 0.890941 10.4613 0.125959C10.475 0.083959 10.4891 0.0424102 10.5033 0.000410156ZM17.0449 10.1226C16.5122 10.1976 15.9846 10.3232 15.4699 10.4998C15.4699 12.9721 13.6635 15.0219 11.2987 15.4021C11.5576 15.4445 11.8224 15.4659 12.0929 15.4659C14.8356 15.4659 17.059 13.2426 17.059 10.4998C17.059 10.3729 17.0543 10.2473 17.0449 10.1226Z"
                                                    fill="#E04848"
                                                />
                                            </g>
                                            <defs>
                                                <clipPath id="clip0">
                                                    <rect
                                                        width="21"
                                                        height="21"
                                                        fill="white"
                                                    />
                                                </clipPath>
                                            </defs>
                                        </svg>
                                        <MuiTypography component="span">
                                            &nbsp;&nbsp;Visist Our Support Desk
                                        </MuiTypography>
                                    </MuiPaper>
                                </MuiGrid>
                            </MuiGrid>
                        </Block>
                    </Block>
                </Block>
            </Block>
        );
    }
}

const mapStateToProps = state => ({
    // ...getEngageAddState(state),
    currentUser: state.default.auth.currentUser,
    pages: state.default.pages.pages,
    totalPages: state.default.pages.totalPages,
    totalSequences: state.default.pages.totalSequences,
    totalSubscribers: state.default.pages.totalSubscribers,
    totalRecentSubscribers: state.default.pages.totalRecentSubscribers,
    updates: state.default.pages.updates,
    tips: state.default.pages.tips
});
const mapDispatchToProps = dispatch => ({
    actions: bindActionCreators(
        {
            getPages
            // updateEngageInfo,
            // updateItemInfo,
            // addStepInfo,
            // updateStepInfo,
            // addEngage,
            // getTags,
            // getPageWorkflowTriggers
        },
        dispatch
    )
});
export default withRouter(
    connect(mapStateToProps, mapDispatchToProps)(Dashboard)
);
