class CreateInterests < ActiveRecord::Migration
  def self.up
    create_table :interests do |t|
      t.string :name
      t.string :category
      t.string :marker_icon
      t.float :latitude
      t.float :longitude
      t.string :url
      t.string :description

      t.timestamps
    end
  end

  def self.down
    drop_table :interests
  end
end
