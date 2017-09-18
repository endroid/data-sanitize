import React from 'react';
import _ from 'lodash';
import Entity from './Entity';

class EntityList extends React.Component {

    constructor(props) {
        super(props);

        this.updateFilter = this.updateFilter.bind(this);
        this.isVisible = this.isVisible.bind(this);

        this.state = { filter: '' };
    }

    updateFilter(event) {
        this.state.filter = event.target.value;
        this.setState(this.state);
    }

    isVisible(entity) {
        if (this.state.filter == '') {
            return true;
        }

        let regex = new RegExp(this.state.filter, 'i');
        for (let field of this.props.fields) {
            if (entity[field].match(regex)) {
                return true;
            }
        }

        return false;
    }

    render() {

        let component = this;

        let headers = [];
        _.each(this.props.fields, function(field, index) {
            headers.push(
                <th key={index}>{field}</th>
            )
        });

        let visibleOptions = [];
        _.each(this.props.entities, function(entity) {
            if (component.isVisible(entity)) {
                visibleOptions.push(
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
            }
        });

        return (
            <div className="box">
                <div className="box-body">
                    <div className="form-group">
                        <form className="form-inline">
                            <div className="form-group">
                                <input className="form-control" type="text" onKeyUp={this.updateFilter} placeholder="Search ..." />
                                &nbsp;
                                <button type="button" className="btn btn-success" onClick={() => this.props.merge()}>Merge</button>
                            </div>
                        </form>
                    </div>
                    <table className="table table-bordered" id="entity-list">
                        <thead>
                        <tr>
                            {headers}
                        </tr>
                        </thead>
                        <tbody>
                            {visibleOptions}
                        </tbody>
                    </table>
                </div>
            </div>
        )
    }
}

export default EntityList;
