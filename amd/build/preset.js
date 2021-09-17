define(['jquery', 'core/modal_factory',  'mod_pulse/modal_preset', 'mod_pulse/events',  'core/str', 'core/fragment', 'core/ajax', 'core/templates'], 
    function($, Modal, ModalPreset, PresetEvents, Str, Fragment, AJAX, Templates) {

    var Preset = function(contextId, courseid, pageparams) {
        this.contextId = contextId;
        this.courseid = courseid;
        this.pageparams = pageparams;

        this.setupmodal();
    };

    Preset.prototype.contextId = 0;
    
    Preset.prototype.courseid = 0;

    Preset.prototype.pageparams = [];

    Preset.prototype.setupmodal = function() {

        var THIS = this;
        // Str.get_string('preset', 'pulse').then(function(langString) {
            var triggerelement = document.querySelectorAll('.pulse-usepreset');
            triggerelement.forEach((element) => element.addEventListener('click', (event) => {
                var presetid = element.getAttribute('data-presetid');
                var params = {'presetid': presetid, 'courseid': THIS.courseid};
                Modal.create({
                    type: ModalPreset.TYPE,
                    body: '',
                    large: true
                }).then(modal => {
                    modal.show();
                   
                    modal.getRoot().on(PresetEvents.customize, (e) => {
                        alert();
                    });

                    modal.getRoot().on(PresetEvents.save, (e) => {
                        e.preventDefault();
                        var formdata = {};
                        var modform = document.forms[0];
                        var modformdata = new FormData(modform);
                        modal.getRoot().get(0).querySelectorAll('form').forEach(form => {
                            var formdata = new FormData(form);
                            if (formdata.get('importmethod') == 'customize') {
                                var formdata = new URLSearchParams(formdata).toString();
                                var pageparams = new URLSearchParams(modformdata).toString();
                                params = {formdata: formdata, pageparams: pageparams};
                                Fragment.loadFragment('mod_pulse', 'apply_preset', THIS.contextId, params ).done((html, js) => {
                                    THIS.handleFormSubmissionResponse(html, js);
                                });
                            } else {
                                this.restorePreset(formdata, THIS.contextId);
                            }
                        });
                        // modal.getRoot().submit();
                    })
                });
            }));
        // });

    };

    Preset.prototype.handleFormSubmissionResponse = (data, js) => {
        console.log(data);
        var modform = document.forms[0];
        var newform = document.createElement('div');
        newform.innerHTML = data;
        // modform.parentNode.replaceChild(newform, modform);
        // runTemplateJS(newJS);
        Templates.replaceNode('[action="modedit.php"]', data, js);
    };

    Preset.prototype.handleFormSubmissionFailure = (data) => {

    };

    
    Preset.prototype.restorePreset = (formdata, contextid, modformdata) => {
        var formdata = new URLSearchParams(formdata).toString();       
        alert(this.contextId);
        var promises = AJAX.call([{
            methodname: 'mod_pulse_apply_presets',
            args: {contextid: contextid, formdata: formdata },
            fail: Preset.prototype.handleFormSubmissionFailure.bind(this)
        }]);

        promises[0].done((response) => {
            Preset.prototype.handleFormSubmissionResponse(response);
        })
    };

    
    return {
        init: (contextId, courseid) => {
            new Preset(contextId, courseid);
        }
    }
});