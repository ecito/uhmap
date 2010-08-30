# Be sure to restart your server when you modify this file.

# Your secret key for verifying cookie session data integrity.
# If you change this key, all old sessions will become invalid!
# Make sure the secret is at least 30 characters and all random, 
# no regular words or you'll be exposed to dictionary attacks.
ActionController::Base.session = {
  :key         => '_uhmap_session',
  :secret      => '2f1614ba5c0d5980125f4166bfe00b8f1b8e560f48d0074157cb5cf2e4289597c071aa8ac47232ffe0d1be61639c7f97d5ab5cea95df7f4c82137dd046b926d7'
}

# Use the database for sessions instead of the cookie-based default,
# which shouldn't be used to store highly confidential information
# (create the session table with "rake db:sessions:create")
# ActionController::Base.session_store = :active_record_store
