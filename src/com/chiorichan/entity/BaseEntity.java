package com.chiorichan.entity;

public abstract class BaseEntity
{
	/**
    * Checks if this player is currently online
    *
    * @return true if they are online
    */
   public abstract boolean isOnline();

   /**
    * Returns the name of this player
    *
    * @return Player name
    */
   public abstract String getName();

   /**
    * Checks if this player is banned or not
    *
    * @return true if banned, otherwise false
    */
   public abstract boolean isBanned();

   /**
    * Bans or unbans this player
    *
    * @param banned true if banned
    */
   public abstract void setBanned(boolean banned);

   /**
    * Checks if this player is whitelisted or not
    *
    * @return true if whitelisted
    */
   public abstract boolean isWhitelisted();

   /**
    * Sets if this player is whitelisted or not
    *
    * @param value true if whitelisted
    */
   public abstract void setWhitelisted(boolean value);

   /**
    * Gets the first date and time that this player was witnessed on this server.
    * <p>
    * If the player has never played before, this will return 0. Otherwise, it will be
    * the amount of milliseconds since midnight, January 1, 1970 UTC.
    *
    * @return Date of first log-in for this player, or 0
    */
   public abstract long getFirstJoined();

   /**
    * Gets the last date and time that this player was witnessed on this server.
    * <p>
    * If the player has never played before, this will return 0. Otherwise, it will be
    * the amount of milliseconds since midnight, January 1, 1970 UTC.
    *
    * @return Date of last log-in for this player, or 0
    */
   public abstract long getLastJoined();

   /**
    * Checks if this player has played on this server before.
    *
    * @return True if the player has played before, otherwise false
    */
   public abstract boolean hasJoinedBefore();
}
