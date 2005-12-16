-- Last edited: Pierpaolo Toniolo 26-07-2005

-- contraints for /navigation

ALTER TABLE category ADD CONSTRAINT FK_parent FOREIGN KEY (parent_id)
      REFERENCES category (category_id) ON DELETE RESTRICT ON UPDATE RESTRICT;

