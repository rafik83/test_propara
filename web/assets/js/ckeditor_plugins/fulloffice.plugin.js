CKEDITOR.plugins.add('strinsert',
    {
        requires : ['richcombo'],
        init : function( editor )
        {
            //  array of strings to choose from that'll be inserted into the editor
            var strings = [];

            strings.push(['{{nom}}', 'Nom de la personne ']);
            strings.push(['{{prenom}}', 'Prénom de la personne']);
            strings.push(['{{logo}}', 'Logo de l\'organisation']);
            strings.push(['{{login}}', 'Login de l\'utilisateur']);
            strings.push(['{{lien_activation[Cliquez Ici]}}', 'Lien d\'activation']);
            strings.push(['{{lien_changement_motdepasse[Cliquez Ici]}}', 'Lien de changement du mot de passe']);
            strings.push(['{{lien_connexion[Cliquez Ici]}}', 'Lien de la connexion']);
            strings.push(['{{mois_fiche_paie}}', 'Le mois de la fiche de paie']);
            strings.push(['{{annee_fiche_paie}}', 'L\'année de la fiche de paie']);

            // add the menu to the editor
            editor.ui.addRichCombo('strinsert',
                {
                    label: 		'Variables',
                    title: 		'Variables',
                    voiceLabel: 'Variables',
                    className: 	'cke_format',
                    multiSelect:false,
                    panel:
                    {
                        css: [ editor.config.contentsCss, CKEDITOR.skin.getPath('editor') ],
                        voiceLabel: editor.lang.panelVoiceLabel
                    },

                    init: function()
                    {
                        this.startGroup( "Variables" );
                        for (var i in strings)
                        {
                            this.add(strings[i][0], strings[i][1], strings[i][2]);
                        }
                    },

                    onClick: function( value )
                    {
                        editor.focus();
                        editor.fire( 'saveSnapshot' );
                        editor.insertHtml(value);
                        editor.fire( 'saveSnapshot' );
                    }
                });
        }
    });

