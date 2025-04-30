class ProgressBar extends React.Component {
    constructor(props) {
        super(props);
    }

    render() {
        let className = 'p-progressbar p-component p-progressbar-determinate';

        return (
            <div role="progressbar" id={this.props.id} className={className} style={this.props.style} aria-valuemin="0"
                 aria-valuenow={this.props.value} aria-valuemax="100" aria-label={this.props.value}>
                <div className="p-progressbar-value p-progressbar-value-animate"
                     style={{width: this.props.value + '%', display: 'block'}}></div>
                <div className="p-progressbar-label">{this.props.value} %</div>
            </div>
        );
    }
}

export default ProgressBar;
