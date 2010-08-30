class CreateParkings < ActiveRecord::Migration
  def self.up
    create_table :parkings do |t|
      t.string :text

      t.timestamps
    end
  end

  def self.down
    drop_table :parkings
  end
end
