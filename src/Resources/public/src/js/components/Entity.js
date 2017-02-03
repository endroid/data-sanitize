import React from 'react';
import _ from 'lodash';

class Entity extends React.Component {

    constructor(props) {
        super(props);
    }

    render() {

        let component = this;

        let values = [];
        _.each(this.props.fields, function(field, index) {
            values.push(
                <td key={index}>{component.props.entity[field]}</td>
            )
        });

        let style = {};
        if (this.props.isTarget) {
            style = { backgroundColor: '#CFC' };
        } else if (this.props.isSource) {
            style = { backgroundColor: '#FFC' };
        }

        return (
            <tr style={style} onClick={() => this.props.toggleSource(this.props.entity.id)} onDoubleClick={() => this.props.toggleTarget(this.props.entity.id)}>
                {values}
            </tr>
        )
    }
}

export default Entity;