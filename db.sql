#
# title: freshcode database schema
# version: 0.2
#


CREATE TABLE [release] ([name] VARCHAR (100) NOT NULL, [title] TEXT NOT
  NULL, [homepage] TEXT, [description] TEXT NOT NULL, [license] VARCHAR (100),
  [tags] VARCHAR (200), [version] VARCHAR (100) NOT NULL, [state] VARCHAR
  (20), [scope] VARCHAR (20), [changes] TEXT, [download] TEXT, [urls] TEXT,
  [autoupdate_module] VARCHAR (20), [autoupdate_url] TEXT, [autoupdate_regex]
  TEXT, [t_published] INT, [t_changed] INT, [flag] INT DEFAULT(0), [deleted]
  BOOLEAN DEFAULT(0), [submitter_openid] TEXT, [submitter] VARCHAR (0, 50),
  [lock] TEXT, [hidden] BOOLEAN DEFAULT(0), [image] TEXT);

CREATE INDEX idx_release ON [release] ( name , version COLLATE NOCASE ,
  t_changed DESC );

CREATE VIEW [release_view] AS SELECT * FROM [release] WHERE NOT deleted AND
  NOT hidden AND flag < 5 GROUP BY version , t_changed ORDER BY t_published
  DESC;

CREATE TABLE flags (name TEXT, reason TEXT, note TEXT, submitter_openid
  TEXT, submitter_ip TEXT);

