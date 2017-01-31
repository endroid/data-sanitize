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

        let buttons = [];
        buttons.push(<input key="toggleSource" type="submit" onClick={() => this.props.toggleSource(this.props.entity.id)} value="Source" />);
        buttons.push(<input key="toggleTarget" type="submit" onClick={() => this.props.toggleTarget(this.props.entity.id)} value="Target" />);

        return (
            <tr style={style}>
                {values}
                <td>{buttons}</td>
            </tr>
        )
    }
}

export default Entity;