class CreateSocialCategories < ActiveRecord::Migration
  def self.up
    create_table :social_categories do |t|
      t.string :title

      t.timestamps
    end
  end

  def self.down
    drop_table :social_categories
  end
end
