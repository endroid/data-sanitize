import React from 'react';
import Request from 'superagent';
import SourceEntitySelect from './SourceEntitySelect';
import TargetEntitySelect from './TargetEntitySelect';
import TargetEntity from './TargetEntity';
import Alert from 'react-s-alert';
import 'react-s-alert/dist/s-alert-default.css';

class Application extends React.Component {

    constructor(props) {
        super(props);

        this.onSourceEntityChange = this.onSourceEntityChange.bind(this);
        this.onTargetEntityChange = this.onTargetEntityChange.bind(this);
        this.onMergeClick = this.onMergeClick.bind(this);

        this.loadState();

        this.state = { entities: [], fields: [], sources: [], target: null };
    }

    loadState() {
        Request.get(this.props.loadPath).then((response) => {
            this.setState(response.body);
        });
    }

    onSourceEntityChange(event) {
        this.state.sources.push(event.target.value);
        this.setState(this.state);
    }

    onTargetEntityChange(event) {
        this.state.target = event.target.value;
        this.setState(this.state);
    }

    onMergeClick(event) {
        console.log(event);
        this.merge();
    }

    merge() {
        Request
            .post(this.props.mergePath)
            .send({ sources: this.state.sources, target: this.state.target })
            .then((response) => {
                if (response.body.success) {
                    Alert.success('Merge completed!', {
                        position: 'bottom',
                        timeout: 4000
                    });
                    this.loadState();
                } else {
                    Alert.warning(response.body.error, {
                        position: 'bottom',
                        timeout: 4000
                    });
                }
            }
        );
    }

    render() {

        let targetEntity = null;
        let sourceEntities = [];
        for (let entity of this.state.entities) {
            if (this.state.sources.includes(entity.id)) {
                sourceEntities.push(entity);
            }
            if (this.state.target == entity.id) {
                targetEntity = entity;
            }
        }

        return (
            <div className="row">
                <Alert stack={{limit: 3}} />
                <div className="col-md-4">
                    <SourceEntitySelect entities={this.state.entities} fields={this.state.fields} onChange={this.onSourceEntityChange} />
                </div>
                <div className="col-md-4">
                    <TargetEntitySelect entities={sourceEntities} fields={this.state.fields} onChange={this.onTargetEntityChange} />
                </div>
                <div className="col-md-4">
                    <TargetEntity entity={targetEntity} fields={this.state.fields} onClick={this.onMergeClick} />
                </div>
            </div>
        );
    }
}

export default Application;
