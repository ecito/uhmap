class CreateStops < ActiveRecord::Migration
  def self.up
    create_table :stops do |t|
      t.string :stop_name
      t.string :stop_desc
      t.string :stop_lat
      t.string :stop_lon
      t.string :zone_id
      t.string :stop_url

    end
  end

  def self.down
    drop_table :stops
  end
end
