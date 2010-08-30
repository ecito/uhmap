class CreateRoutes < ActiveRecord::Migration
  def self.up
    create_table :routes do |t|
      t.integer :agency_id
      t.string :route_short_name
      t.string :route_long_name
      t.string :route_desc
      t.string :route_type
      t.string :route_url
      t.string :route_color
      t.string :route_text_color

    end
  end

  def self.down
    drop_table :routes
  end
end
