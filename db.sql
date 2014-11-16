--# title: freshcode database schema
--# version: 0.7

CREATE TABLE [release] ( 
    name              VARCHAR( 100 )     NOT NULL,
    title             TEXT               NOT NULL,
    homepage          TEXT,
    description       TEXT               NOT NULL,
    license           VARCHAR( 100 ),
    tags              VARCHAR( 200 ),
    version           VARCHAR( 100 )     NOT NULL,
    state             VARCHAR( 20 ),
    scope             VARCHAR( 20 ),
    changes           TEXT,
    download          TEXT,
    urls              TEXT,
    autoupdate_module VARCHAR( 20 ),
    autoupdate_url    TEXT,
    autoupdate_regex  TEXT,
    t_published       INT,
    t_changed         INT,
    flag              INT                DEFAULT ( 0 ),
    deleted           BOOLEAN            DEFAULT ( 0 ),
    submitter_openid  TEXT,
    submitter         VARCHAR( 0, 100 ),
    lock              TEXT,
    hidden            BOOLEAN            DEFAULT ( 0 ),
    image             TEXT,
    social_links      INT                DEFAULT ( 0 ),
    submitter_image   VARCHAR( 200 ),
    via               VARCHAR( 16 ),
    editor_note       TEXT,
    autoupdate_delay  REAL,
    CONSTRAINT 'release_revisions' UNIQUE ( name, version COLLATE 'NOCASE', t_published, t_changed ) 
);

CREATE TABLE flags ( 
    name             TEXT,
    reason           TEXT,
    note             TEXT,
    submitter_openid TEXT,
    submitter_ip     TEXT 
);

CREATE TABLE tags ( 
    name VARCHAR( 1, 33 ),
    tag  VARCHAR( 1, 33 ) 
);

CREATE INDEX idx_release ON [release] ( 
    name,
    t_changed                  DESC,
    t_published                DESC,
    version     COLLATE NOCASE 
);


CREATE VIEW release_ordered AS
       SELECT *
         FROM [release]
        ORDER BY t_published DESC,
                  t_changed DESC;

CREATE VIEW release_versions AS
       SELECT *,
              MAX( t_changed ) AS _order
         FROM release_ordered
        WHERE NOT deleted
        GROUP BY name,
                 version
        ORDER BY t_published DESC;


