package com.chiorichan.command.defaults;

import java.util.Arrays;

import com.chiorichan.ChioriFramework;
import com.chiorichan.ChatColor;
import com.chiorichan.command.Command;
import com.chiorichan.command.CommandSender;

public class ReloadCommand extends ChioriFrameworkCommand {
    public ReloadCommand(String name) {
        super(name);
        this.description = "Reloads the server configuration and plugins";
        this.usageMessage = "/reload";
        this.setPermission("bukkit.command.reload");
        this.setAliases(Arrays.asList("rl"));
    }

    @Override
    public boolean execute(CommandSender sender, String currentAlias, String[] args) {
        if (!testPermission(sender)) return true;

        ChioriFramework.reload();
        Command.broadcastCommandMessage(sender, ChatColor.GREEN + "Reload complete.");

        return true;
    }
}
