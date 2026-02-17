use serde::{Deserialize, Serialize};

#[derive(Serialize, Deserialize)]
pub struct NameServers {
    #[serde(skip_serializing_if = "Option::is_none")]
    pub addresses: Option<Vec<String>>,
}

#[derive(Serialize, Deserialize)]
pub struct Enp0s {
    #[serde(with = "dhcp4_serde")]
    pub dhcp4: bool,
    #[serde(skip_serializing_if = "Option::is_none")]
    pub addresses: Option<Vec<String>>,
    #[serde(skip_serializing_if = "Option::is_none")]
    pub gateway4: Option<String>,
    #[serde(skip_serializing_if = "Option::is_none")]
    pub nameservers: Option<NameServers>,
}

#[derive(Serialize, Deserialize)]
pub struct Ethernets {
    pub enp0s3: Enp0s,
}

#[derive(Serialize, Deserialize)]
pub struct Network {
    pub ethernets: Ethernets,
    pub version: i8,
}

#[derive(Serialize, Deserialize)]
pub struct Config {
    pub network: Network,
}

mod dhcp4_serde {
    use serde::de::Error;
    use serde::{self, Deserialize, Deserializer, Serializer};

    pub fn serialize<S>(value: &bool, serializer: S) -> Result<S::Ok, S::Error>
    where
        S: Serializer,
    {
        if *value {
            serializer.serialize_bool(true)
        } else {
            serializer.serialize_str("no")
        }
    }

    pub fn deserialize<'de, D>(deserializer: D) -> Result<bool, D::Error>
    where
        D: Deserializer<'de>,
    {
        #[derive(Deserialize)]
        #[serde(untagged)]
        enum BoolOrStr {
            Bool(bool),
            Str(String),
        }

        match BoolOrStr::deserialize(deserializer)? {
            BoolOrStr::Bool(b) => Ok(b),
            BoolOrStr::Str(s) if s == "no" => Ok(false),
            BoolOrStr::Str(s) if s == "yes" => Ok(true), // bonus
            BoolOrStr::Str(s) => Err(D::Error::custom(format!("unexpected value: {s}"))),
        }
    }
}
