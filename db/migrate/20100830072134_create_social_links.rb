class CreateSocialLinks < ActiveRecord::Migration
  def self.up
    create_table :social_links do |t|
      t.string :title
      t.string :url
      t.string :network
      t.string :icon
      
      t.timestamps
    end
  end

  def self.down
    drop_table :social_links
  end
end
