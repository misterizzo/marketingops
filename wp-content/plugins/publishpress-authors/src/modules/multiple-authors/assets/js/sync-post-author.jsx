import DataMigrationBox from './Components/DataMigrationBox.jsx';

let {__} = wp.i18n;

jQuery(function () {

    let messages = {};

    ReactDOM.render(
        <DataMigrationBox nonce={ppmaSyncPostAuthor.nonce}
                          chunkSize={ppmaSyncPostAuthor.chunkSize}
                          actionGetInitialData={'get_sync_post_author_data'}
                          actionMigrationStep={'sync_post_author'}
                          actionFinishProcess={'finish_sync_post_author'}
                          buttonLabel={ppmaSyncPostAuthor.buttonLabel}
                          messageCollectingData={ppmaSyncPostAuthor.messageCollectingData}
                          messageEndingProcess={ppmaSyncPostAuthor.messageEndingProcess}
                          messageDone={ppmaSyncPostAuthor.messageDone}
                          messageWait={ppmaSyncPostAuthor.messageWait}
                          messageStarting={ppmaSyncPostAuthor.messageStarting}
                          messageProgress={ppmaSyncPostAuthor.messageProgress}
        />,
        document.getElementById('publishpress-authors-sync-post-authors')
    );
});
