package com.chiorichan.command.defaults;

import com.chiorichan.ChioriFramework;
import com.chiorichan.ChatColor;
import com.chiorichan.command.CommandSender;

public class TestForCommand extends VanillaCommand {
    public TestForCommand() {
        super("testfor");
        this.description = "Tests whether a specifed player is online";
        this.usageMessage = "/testfor <player>";
        this.setPermission("bukkit.command.testfor");
    }

    @Override
    public boolean execute(CommandSender sender, String currentAlias, String[] args) {
        if (!testPermission(sender)) return true;
        if (args.length < 1)  {
            sender.sendMessage(ChatColor.RED + "Usage: " + usageMessage);
            return false;
        }

        sender.sendMessage(ChatColor.RED + "/testfor is only usable by commandblocks with analog output.");
        return true;
    }
}
