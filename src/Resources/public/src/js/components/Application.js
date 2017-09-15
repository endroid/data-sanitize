import React from 'react';
import Request from 'superagent';
import Alert from 'react-s-alert';
import 'react-s-alert/dist/s-alert-default.css';
import Noty from 'noty';
import 'noty/lib/noty.css';
import EntityList from './EntityList';

class Application extends React.Component {

    constructor(props) {
        super(props);

        this.toggleSource = this.toggleSource.bind(this);
        this.toggleTarget = this.toggleTarget.bind(this);
        this.merge = this.merge.bind(this);

        this.loadState();

        this.state = { entities: [], fields: [], sources: [], target: null };
    }

    loadState() {
        Request.get(this.props.loadPath).then((response) => {
            this.setState(response.body);
        });
    }

    toggleSource(id) {
        let index = this.state.sources.indexOf(id);

        if (index == -1) {
            this.state.sources.push(id);
        } else if (id != this.state.target) {
            this.state.sources.splice(index, 1);
        }

        this.setState(this.state);
    }

    toggleTarget(id) {
        if (this.state.target == id) {
            this.state.target = null;
        } else {
            this.state.target = id;
        }
        this.setState(this.state);
    }

    merge(confirmed = false) {

        if (this.state.target == null) {
            return;
        }

        if (!confirmed) {
            let component = this;
            let noty = new Noty({
                text: 'Are you sure?',
                buttons: [
                    Noty.button('Yes', 'btn btn-success', function () {
                        component.merge(true);
                        noty.close();
                    }),
                    Noty.button('No', 'btn btn-danger', function () {
                        noty.close();
                    })
                ]
            }).show();

            return;
        }

        Request
            .post(this.props.mergePath)
            .type('form')
            .send({ 'sources[]': this.state.sources, target: this.state.target })
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
        let target = null;
        let sources = [];
        for (let entity of this.state.entities) {
            if (this.state.sources.includes(entity.id)) {
                sources.push(entity.id);
            }
            if (this.state.target == entity.id) {
                target = entity.id;
            }
        }

        return (
            <div className="row">
                <Alert stack={{ limit: 3 }} />
                <div className="col-md-12">
                    <EntityList
                        entities={this.state.entities}
                        fields={this.state.fields}
                        sources={sources}
                        target={target}
                        toggleSource={this.toggleSource}
                        toggleTarget={this.toggleTarget}
                        merge={this.merge}
                    />
                </div>
            </div>
        );
    }
}

export default Application;