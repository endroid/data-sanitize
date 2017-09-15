import React from 'react';
import _ from 'lodash';

class Entity extends React.Component {

    constructor(props) {
        super(props);

        this.toggle = this.toggle.bind(this);
    }

    toggle()
    {
        if (this.props.isSource || this.props.isTarget) {
            this.props.toggleTarget(this.props.entity.id);
        }

        this.props.toggleSource(this.props.entity.id);
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
            style = { backgroundColor: '#28a745' };
        } else if (this.props.isSource) {
            style = { backgroundColor: '#dc3545' };
        }

        return (
            <tr style={style} onClick={() => this.toggle()}>
                {values}
            </tr>
        )
    }
}

export default Entity;