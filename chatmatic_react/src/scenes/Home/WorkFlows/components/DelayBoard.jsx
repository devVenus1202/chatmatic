import React, { Component, Fragment } from 'react';
import { connect } from 'react-redux';
import { Image, Icon } from 'semantic-ui-react';

import { Block, Svg } from '../../Layout';

class DelayBoard extends Component {
    render() {
        return (
            <React.Fragment>
                <Block className="imageTextBlockMain delay-board-block">
                    <button className="btn btn-link btn-add-reply">
                        <Icon name="clock outline" /> Typing 3 sec...
                    </button>
                </Block>
            </React.Fragment>
        );
    }
}

export default connect(state => ({}))(DelayBoard);
