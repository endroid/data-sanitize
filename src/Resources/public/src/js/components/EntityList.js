import React from 'react';
import _ from 'lodash';
import Entity from './Entity';

class EntityList extends React.Component {

    constructor(props) {
        super(props);
    }

    render() {

        let component = this;

        let headers = [];
        _.each(this.props.fields, function(field, index) {
            headers.push(
                <th key={index}>{field}</th>
            )
        });

        let options = [];
        _.each(this.props.entities, function(entity) {
            options.push(
                <Entity
                    entity={entity}
                    key={entity.id}
                    fields={component.props.fields}
                    isSource={component.props.sources.indexOf(entity.id) != -1}
                    isTarget={component.props.target == entity.id}
                    toggleSource={component.props.toggleSource}
                    toggleTarget={component.props.toggleTarget}
                />
            );
        });

        return (
            <div className="box">
                <div className="box-body">
                    <table className="table table-bordered" id="entity-list">
                        <thead>
                        <tr>
                            {headers}
                            <td>&nbsp;</td>
                        </tr>
                        </thead>
                        <tbody>
                            {options}
                        </tbody>
                    </table>
                </div>
            </div>
        )
    }
}

export default EntityList;
