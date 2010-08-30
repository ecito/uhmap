class AddSocialCategoryIdToSocialLink < ActiveRecord::Migration
  def self.up
    add_column :social_links, :social_category_id, :integer
  end

  def self.down
    remove_column :social_links, :social_category_id
  end
end
